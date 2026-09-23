<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\AuditLog;
use common\models\Branch;
use common\models\Department;
use common\models\ReceptionShift;
use common\models\User;
use common\models\Visit;
use common\models\Visitor;
use common\services\AuditLogService;
use common\services\BranchCatalog;
use Yii;
use yii\helpers\Html;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class AdminController extends BaseController
{
    public function actionDashboard(): string
    {
        $this->requireRole(User::ROLE_ADMIN);

        $branches = BranchCatalog::all();
        $selectedBranch = trim((string) Yii::$app->request->get('branch', ''));
        if ($selectedBranch !== '' && !array_key_exists($selectedBranch, $branches)) {
            $selectedBranch = '';
        }

        try {
            $data = $this->dashboardStats($selectedBranch);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $data = self::emptyDashboardStats($branches, $selectedBranch);
        }
        return $this->render('dashboard', $data);
    }

    /**
     * Visitor/user counters and recent rows for the admin dashboard.
     *
     * @return array<string, mixed>
     */
    private function dashboardStats(string $selectedBranch): array
    {
        $branches = BranchCatalog::all();
        $visitQuery = Visit::find();
        $activeQuery = Visit::find()->where(['status' => Visit::STATUS_CHECKED_IN, 'check_out_time' => null]);
        $checkedOutQuery = Visit::find()->where(['not', ['check_out_time' => null]]);
        $todayQuery = Visit::find()->where(['>=', 'check_in_time', date('Y-m-d 00:00:00')]);
        $pendingQuery = Visit::find()->where(['not in', 'status', [Visit::STATUS_CHECKED_IN, Visit::STATUS_CHECKED_OUT]]);
        $userQuery = User::find();
        $visitorQuery = Visit::find()->alias('v')->select('v.visitor_id')->distinct();
        foreach ([$visitQuery, $activeQuery, $checkedOutQuery, $todayQuery, $pendingQuery, $userQuery, $visitorQuery] as $query) {
            if ($selectedBranch !== '') {
                $query->andWhere(['branch_code' => $selectedBranch]);
            }
        }

        return [
            'totalUsers' => (int) $userQuery->count(),
            'pendingUsers' => (int) User::find()->where(['role' => User::ROLE_RECEPTION, 'status' => User::STATUS_INACTIVE])->count(),
            'totalVisitors' => (int) $visitorQuery->count(),
            'totalVisits' => (int) $visitQuery->count(),
            'activeVisits' => (int) $activeQuery->count(),
            'checkedOutVisits' => (int) $checkedOutQuery->count(),
            'pendingVisits' => (int) $pendingQuery->count(),
            'todayVisits' => (int) $todayQuery->count(),
            'recentVisitors' => $visitQuery->with(['visitor', 'host'])->orderBy(['created_at' => SORT_DESC])->limit(10)->all(),
            'branches' => $branches,
            'selectedBranch' => $selectedBranch,
        ];
    }

    /**
     * Zeroed dashboard payload used when the stats queries fail.
     *
     * @param array<string, string> $branches
     * @return array<string, mixed>
     */
    private static function emptyDashboardStats(array $branches, string $selectedBranch): array
    {
        return array_map(
            static fn ($value) => is_int($value) ? 0 : (is_array($value) ? [] : $value),
            [
                'totalUsers' => 0,
                'pendingUsers' => 0,
                'totalVisitors' => 0,
                'totalVisits' => 0,
                'activeVisits' => 0,
                'checkedOutVisits' => 0,
                'pendingVisits' => 0,
                'todayVisits' => 0,
                'recentVisitors' => [],
                'branches' => $branches,
                'selectedBranch' => $selectedBranch,
            ]
        );
    }

    public function actionBranches(): string
    {
        $this->requireRole(User::ROLE_ADMIN);

        return $this->render('branches', [
            'branches' => BranchCatalog::all(),
            'departments' => BranchCatalog::departments(),
            'branchModels' => Branch::find()->with('departments')->where(['status' => 1])->orderBy(['name' => SORT_ASC])->all(),
        ]);
    }

    public function actionAddBranch(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $model = new Branch();
        $model->load(Yii::$app->request->post());
        $model->code = self::slugify((string) $model->name);
        if ($model->validate() && $model->save(false)) {
            AuditLogService::logAction('create-branch', 'Branch ' . $model->name . ' created.');
            Yii::$app->session->setFlash('success', 'Branch added successfully.');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['branches']);
    }

    public function actionUploadBranches(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $file = UploadedFile::getInstanceByName('branches_file');
        if ($file === null) {
            Yii::$app->session->setFlash('error', 'Please choose a CSV or Excel file.');
            return $this->redirect(['branches']);
        }

        $extension = strtolower((string) $file->extension);
        if (!in_array($extension, ['csv', 'xlsx', 'xls'], true)) {
            Yii::$app->session->setFlash('error', 'Only CSV, XLSX, and XLS files are supported.');
            return $this->redirect(['branches']);
        }

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->tempName);
            $reader->setReadDataOnly(true);
            $sheet = $reader->load($file->tempName)->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            $created = 0;
            $skipped = 0;

            foreach ($rows as $rowNumber => $row) {
                if ($rowNumber === 1 && strtolower(trim((string) ($row['A'] ?? ''))) === 'code') {
                    continue;
                }
                $code = strtolower(trim((string) ($row['A'] ?? '')));
                $name = trim((string) ($row['B'] ?? ''));
                if ($code === '' || $name === '' || Branch::find()->where(['or', ['code' => $code], ['name' => $name]])->exists()) {
                    $skipped++;
                    continue;
                }

                $branch = new Branch([
                    'code' => $code,
                    'name' => $name,
                    'status' => (int) (($row['C'] ?? '') !== '' ? $row['C'] : 1),
                ]);
                if ($branch->validate() && $branch->save(false)) {
                    $created++;
                } else {
                    $skipped++;
                }
            }

            AuditLogService::logAction('upload-branches', 'Uploaded branches file: ' . $created . ' created, ' . $skipped . ' skipped.');
            Yii::$app->session->setFlash('success', 'Branch upload complete: ' . $created . ' created, ' . $skipped . ' skipped.');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'Unable to read the branch file. Use columns: code, name, status.');
        }

        return $this->redirect(['branches']);
    }

    public function actionDownloadBranchTemplate(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        return Yii::$app->response->sendContentAsFile(
            "code,name,status\npbz-stone-town,PBZ Stone Town Branch,1\n",
            'branches-template.csv',
            ['mimeType' => 'text/csv'],
        );
    }

    public function actionAddDepartment(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $model = new Department();
        $model->load(Yii::$app->request->post());
        $model->code = self::slugify((string) $model->name);
        if ($model->validate() && $model->save(false)) {
            AuditLogService::logAction('create-department', 'Department ' . $model->name . ' created.');
            Yii::$app->session->setFlash('success', 'Department added successfully.');
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()));
        }
        return $this->redirect(['branches']);
    }

    public function actionViewBranch(int $id): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        return $this->render('branch-view', ['model' => $this->findBranch($id)]);
    }

    public function actionUpdateBranch(int $id): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $model = $this->findBranch($id);
        $code = $model->code;
        if ($model->load(Yii::$app->request->post())) {
            $model->code = $code;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Branch updated successfully.');
                return $this->redirect(['view-branch', 'id' => $model->id]);
            }
        }
        return $this->render('branch-update', ['model' => $model]);
    }

    public function actionDeleteBranch(int $id): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $model = $this->findBranch($id);
        $inUse = User::find()->where(['branch_code' => $model->code])->exists()
            || Visit::find()->where(['branch_code' => $model->code])->exists();
        if ($inUse) {
            Yii::$app->session->setFlash('error', 'This branch cannot be deleted because it has users or visitor records.');
            return $this->redirect(['branches']);
        }
        $model->delete();
        Yii::$app->session->setFlash('success', 'Branch deleted successfully.');
        return $this->redirect(['branches']);
    }

    public function actionUpdateDepartment(int $id): string|Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $model = $this->findDepartment($id);
        $code = $model->code;
        if ($model->load(Yii::$app->request->post())) {
            $model->code = $code;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Department updated successfully.');
                return $this->redirect(['view-branch', 'id' => $model->branch_id]);
            }
        }
        return $this->render('department-update', ['model' => $model]);
    }

    public function actionDeleteDepartment(int $id): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        $model = $this->findDepartment($id);
        $branchId = $model->branch_id;
        $model->delete();
        Yii::$app->session->setFlash('success', 'Department deleted successfully.');
        return $this->redirect(['view-branch', 'id' => $branchId]);
    }

    /**
     * Build a URL-safe code from a human-readable name.
     */
    private static function slugify(string $name): string
    {
        return strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
    }

    private function findBranch(int $id): Branch
    {        $model = Branch::findOne($id);
        if ($model === null) {
            throw new \yii\web\NotFoundHttpException('Branch not found.');
        }
        return $model;
    }

    private function findDepartment(int $id): Department
    {
        $model = Department::findOne($id);
        if ($model === null) {
            throw new \yii\web\NotFoundHttpException('Department not found.');
        }
        return $model;
    }

    public function actionStats(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);

        Yii::$app->response->format = Response::FORMAT_JSON;
        $today = date('Y-m-d 00:00:00');

        try {
            return $this->asJson([
                'total' => (int) Visit::find()->count(),
                'inside' => (int) Visit::find()->where(['check_out_time' => null])->count(),
                'checked_out' => (int) Visit::find()->where(['not', ['check_out_time' => null]])->count(),
                'today' => (int) Visit::find()->where(['>=', 'check_in_time', $today])->count(),
            ]);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return $this->asJson([
                'total' => 0,
                'inside' => 0,
                'checked_out' => 0,
                'today' => 0,
            ]);
        }
    }

    public function actionActivity(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);

        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $logs = AuditLog::find()
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(10)
                ->all();

            return $this->asJson(array_map(static function (AuditLog $log): array {
                $timestamp = strtotime((string) $log->created_at);

                return [
                    'action' => (string) $log->action,
                    'description' => (string) $log->description,
                    'time' => $timestamp === false ? (string) $log->created_at : date('H:i:s', $timestamp),
                ];
            }, $logs));
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return $this->asJson([]);
        }
    }

    public function actionReports(): string
    {
        $this->requireRole(User::ROLE_ADMIN);

        return $this->render('reports');
    }

    public function actionShifts(): string
    {
        $this->requireRole(User::ROLE_ADMIN);

        return $this->render('shifts', [
            'shifts' => ReceptionShift::find()->with('user')->orderBy(['started_at' => SORT_DESC, 'id' => SORT_DESC])->all(),
        ]);
    }

    public function actionDaily(): string
    {
        return $this->renderTimeReport('daily', 'Daily Reports', 'daily');
    }

    public function actionWeekly(): string
    {
        return $this->renderTimeReport('weekly', 'Weekly Reports', 'weekly');
    }

    public function actionMonthly(): string
    {
        return $this->renderTimeReport('monthly', 'Monthly Reports', 'monthly');
    }

    public function actionAnnual(): string
    {
        return $this->renderTimeReport('annual', 'Annual Reports', 'annual');
    }

    public function actionExportPdf(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);

        try {
            if (!class_exists('Mpdf\\Mpdf')) {
                throw new ServerErrorHttpException('PDF export dependency is not installed.');
            }

            [$visits, $startDate, $endDate, $filterType, $filterValue, $filters] = $this->timeReportData();
            $html = '<h1>Visitor Report</h1>'
                . '<p>Period: ' . Html::encode($startDate->format('Y-m-d')) . ' to ' . Html::encode($endDate->format('Y-m-d')) . '</p>'
                . '<table border="1" cellpadding="6" cellspacing="0" width="100%">'
                . '<thead><tr><th>Name</th><th>Branch</th><th>Department</th><th>Checked In By</th><th>Checked Out By</th><th>Status</th><th>Date</th></tr></thead><tbody>';
            foreach ($this->reportRows($visits, $filters) as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . Html::encode($cell) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

            $pdf = new \Mpdf\Mpdf();
            $pdf->WriteHTML($html);

            return Yii::$app->response->sendContentAsFile(
                $pdf->Output('', 'S'),
                'visitor-' . $filterType . '-report.pdf',
                ['mimeType' => 'application/pdf'],
            );
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'The PDF report is temporarily unavailable.');

            return $this->redirect(['reports']);
        }
    }

    public function actionExportExcel(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);

        try {
            if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                throw new ServerErrorHttpException('Excel export dependency is not installed.');
            }

            [$visits, , , $filterType, , $filters] = $this->timeReportData();
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray(['Name', 'Branch', 'Department', 'Checked In By', 'Checked Out By', 'Status', 'Date'], null, 'A1');
            $rows = $this->reportRows($visits, $filters);
            if ($rows !== []) {
                $sheet->fromArray($rows, null, 'A2');
            }
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);
            foreach (range('A', 'G') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');

            return Yii::$app->response->sendContentAsFile(
                (string) ob_get_clean(),
                'visitor-' . $filterType . '-report.xlsx',
                ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            );
        } catch (\Throwable $exception) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'The Excel report is temporarily unavailable.');

            return $this->redirect(['reports']);
        }
    }

    /**
     * The visitor rows shared by the PDF and Excel exports.
     *
     * @param array<int, Visit> $visits
     * @param array<string, mixed> $filters
     * @return array<int, array<int, string>>
     */
    private function reportRows(array $visits, array $filters): array
    {
        $rows = [];
        foreach ($visits as $visit) {
            $rows[] = [
                (string) ($visit->visitor?->full_name ?? 'Unknown visitor'),
                (string) ($filters['branches'][$visit->branch_code] ?? 'Unassigned'),
                (string) ($filters['departments'][$visit->branch_code][$visit->department_code] ?? '—'),
                (string) ($visit->checkedInBy?->username ?? 'Unknown / legacy'),
                (string) ($visit->checkedOutBy?->username ?? ($visit->check_out_time ? 'Unknown / legacy' : '—')),
                $visit->isCheckedIn() ? 'Inside' : 'Checked out',
                (string) ($visit->check_in_time ?: ''),
            ];
        }

        return $rows;
    }

    private function renderTimeReport(string $view, string $title, string $filterType): string
    {        $this->requireRole(User::ROLE_ADMIN);

        [$visits, $startDate, $endDate, $filterType, $filterValue, $filters] = $this->timeReportData($filterType);

        return $this->render($view, [
            'title' => $title,
            'visits' => $visits,
            'filterType' => $filterType,
            'filterValue' => $filterValue,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filters' => $filters,
        ]);
    }

    /** @return array{0: array, 1: \DateTimeImmutable, 2: \DateTimeImmutable, 3: string, 4: string, 5: array} */
    private function timeReportData(string|null $forcedType = null): array
    {
        $filterType = $forcedType ?? (string) Yii::$app->request->get('type', 'daily');
        if (!in_array($filterType, ['daily', 'weekly', 'monthly', 'annual'], true)) {
            $filterType = 'daily';
        }

        [$startDate, $endDate, $filterValue] = $this->resolveDateRange($filterType);

        $start = $startDate->getTimestamp();
        $end = $endDate->getTimestamp();
        $branches = BranchCatalog::all();
        $departments = BranchCatalog::departments();
        $branch = trim((string) Yii::$app->request->get('branch', ''));
        $department = trim((string) Yii::$app->request->get('department', ''));
        $receptionId = (int) Yii::$app->request->get('reception_id', 0);
        $visits = Visit::find()
            ->with(['visitor', 'host', 'checkedInBy', 'checkedOutBy'])
            ->where(['between', 'created_at', $start, $end])
            ->andFilterWhere($branch !== '' && array_key_exists($branch, $branches) ? ['branch_code' => $branch] : [])
            ->andFilterWhere($department !== '' ? ['department_code' => $department] : [])
            ->andFilterWhere($receptionId > 0 ? ['or', ['checked_in_by_user_id' => $receptionId], ['checked_out_by_user_id' => $receptionId]] : [])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        $receptionQuery = User::find()->where(['role' => User::ROLE_RECEPTION, 'status' => User::STATUS_ACTIVE]);
        if ($branch !== '' && array_key_exists($branch, $branches)) {
            $receptionQuery->andWhere(['branch_code' => $branch]);
        }
        $receptions = $receptionQuery->orderBy(['username' => SORT_ASC])->all();
        return [$visits, $startDate, $endDate, $filterType, $filterValue, ['branches' => $branches, 'departments' => $departments, 'receptions' => $receptions, 'branch' => $branch, 'department' => $department, 'receptionId' => $receptionId]];
    }

    private function filterValue(string $filterType): string
    {
        return match ($filterType) {
            'weekly' => (string) Yii::$app->request->get('date', ''),
            'monthly' => (string) Yii::$app->request->get('month', ''),
            'annual' => (string) Yii::$app->request->get('year', ''),
            default => (string) Yii::$app->request->get('date', date('Y-m-d')),
        };
    }

    /**
     * Parse and clamp the requested value into a valid, non-future date.
     *
     * @return array{0: \DateTimeImmutable, 1: string} the clamped date and its canonical filter value
     */
    private function clampDate(string $filterType, string $format): array
    {
        $filterValue = $this->filterValue($filterType);
        $today = new \DateTimeImmutable('today');
        $selected = \DateTimeImmutable::createFromFormat('!' . $format, $filterValue);
        $upperBound = match ($filterType) {
            'monthly' => $today->modify('first day of this month'),
            default => $today,
        };
        if (!$selected || $selected->format($format) !== $filterValue || $selected > $upperBound) {
            $selected = $upperBound;
            $filterValue = $selected->format($format);
        }

        return [$selected, $filterValue];
    }

    /** @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable, 2: string} */
    private function resolveDateRange(string $filterType): array
    {
        $today = new \DateTimeImmutable('today');

        if ($filterType === 'weekly') {
            [$selectedDate, $filterValue] = $this->clampDate('weekly', 'Y-m-d');
            $startDate = $selectedDate->modify('monday this week');
            $endDate = $startDate->modify('+6 days')->setTime(23, 59, 59);
        } elseif ($filterType === 'monthly') {
            [$selectedMonth, $filterValue] = $this->clampDate('monthly', 'Y-m');
            $startDate = $selectedMonth->modify('first day of this month');
            $endDate = $startDate->modify('first day of next month')->modify('-1 second');
        } elseif ($filterType === 'annual') {
            $filterValue = $this->filterValue('annual');
            $currentYear = (int) $today->format('Y');
            $selectedYear = filter_var($filterValue, FILTER_VALIDATE_INT);
            if ($selectedYear === false || $selectedYear < 1970 || $selectedYear > $currentYear || (string) $selectedYear !== $filterValue) {
                $selectedYear = $currentYear;
                $filterValue = (string) $currentYear;
            }
            $startDate = new \DateTimeImmutable($selectedYear . '-01-01');
            $endDate = $startDate->modify('+1 year')->modify('-1 second');
        } else {
            [$selectedDate, $filterValue] = $this->clampDate('daily', 'Y-m-d');
            $startDate = $selectedDate;
            $endDate = $selectedDate->setTime(23, 59, 59);
        }

        return [$startDate, $endDate, $filterValue];
    }
}
