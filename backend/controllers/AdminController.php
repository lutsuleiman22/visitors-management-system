<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use common\models\AuditLog;
use common\models\Notification;
use common\models\User;
use common\models\Visit;
use common\models\Visitor;
use Yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class AdminController extends BaseController
{
    public function actionDashboard(): string
    {
        $this->requireRole(User::ROLE_ADMIN);

        try {
            $data = [
                'totalUsers' => (int) User::find()->count(),
                'totalVisitors' => (int) Visitor::find()->count(),
                'totalVisits' => (int) Visit::find()->count(),
                'activeVisits' => (int) Visit::find()->where(['status' => Visit::STATUS_CHECKED_IN, 'check_out_time' => null])->count(),
                'checkedOutVisits' => (int) Visit::find()->where(['not', ['check_out_time' => null]])->count(),
                'pendingVisits' => (int) Visit::find()->where(['not in', 'status', [Visit::STATUS_CHECKED_IN, Visit::STATUS_CHECKED_OUT]])->count(),
                'todayVisits' => (int) Visit::find()->where(['>=', 'check_in_time', date('Y-m-d 00:00:00')])->count(),
                'recentVisitors' => Visit::find()->with(['visitor', 'host'])->orderBy(['created_at' => SORT_DESC])->limit(10)->all(),
            ];
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            $data = [
                'totalUsers' => 0,
                'totalVisitors' => 0,
                'totalVisits' => 0,
                'activeVisits' => 0,
                'checkedOutVisits' => 0,
                'pendingVisits' => 0,
                'todayVisits' => 0,
                'recentVisitors' => [],
            ];
        }
        return $this->render('dashboard', $data);
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

            [$visits, $startDate, $endDate, $filterType] = $this->timeReportData();
            $html = '<h1>Visitor Report</h1>'
                . '<p>Period: ' . Html::encode($startDate->format('Y-m-d')) . ' to ' . Html::encode($endDate->format('Y-m-d')) . '</p>'
                . '<table border="1" cellpadding="6" cellspacing="0" width="100%">'
                . '<thead><tr><th>Name</th><th>Host</th><th>Status</th><th>Date</th></tr></thead><tbody>';
            foreach ($visits as $visit) {
                $html .= '<tr><td>' . Html::encode($visit->visitor?->full_name ?? 'Unknown visitor')
                    . '</td><td>' . Html::encode($visit->host?->username ?? 'Unassigned')
                    . '</td><td>' . Html::encode($visit->isCheckedIn() ? 'Inside' : 'Checked out')
                    . '</td><td>' . Html::encode($visit->check_in_time ?: '—') . '</td></tr>';
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

            [$visits, , , $filterType] = $this->timeReportData();
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray(['Name', 'Host', 'Status', 'Date'], null, 'A1');
            $rows = [];
            foreach ($visits as $visit) {
                $rows[] = [
                    (string) ($visit->visitor?->full_name ?? 'Unknown visitor'),
                    (string) ($visit->host?->username ?? 'Unassigned'),
                    $visit->isCheckedIn() ? 'Inside' : 'Checked out',
                    (string) ($visit->check_in_time ?: ''),
                ];
            }
            if ($rows !== []) {
                $sheet->fromArray($rows, null, 'A2');
            }
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);
            foreach (range('A', 'D') as $column) {
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

    private function renderTimeReport(string $view, string $title, string $filterType): string
    {
        $this->requireRole(User::ROLE_ADMIN);

        [$visits, $startDate, $endDate, $filterType, $filterValue] = $this->timeReportData($filterType);

        return $this->render($view, [
            'title' => $title,
            'visits' => $visits,
            'filterType' => $filterType,
            'filterValue' => $filterValue,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }

    /** @return array{0: array, 1: \DateTimeImmutable, 2: \DateTimeImmutable, 3: string, 4: string} */
    private function timeReportData(string|null $forcedType = null): array
    {
        $filterType = $forcedType ?? (string) Yii::$app->request->get('type', 'daily');
        if (!in_array($filterType, ['daily', 'weekly', 'monthly', 'annual'], true)) {
            $filterType = 'daily';
        }

        $today = new \DateTimeImmutable('today');
        if ($filterType === 'weekly') {
            $filterValue = $this->filterValue($filterType);
            $selectedDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $filterValue);
            if (!$selectedDate || $selectedDate->format('Y-m-d') !== $filterValue || $selectedDate > $today) {
                $selectedDate = $today;
                $filterValue = $today->format('Y-m-d');
            }
            $startDate = $selectedDate->modify('monday this week');
            $endDate = $startDate->modify('+6 days')->setTime(23, 59, 59);
        } elseif ($filterType === 'monthly') {
            $filterValue = $this->filterValue($filterType);
            $selectedMonth = \DateTimeImmutable::createFromFormat('!Y-m', $filterValue);
            if (!$selectedMonth || $selectedMonth->format('Y-m') !== $filterValue || $selectedMonth > $today->modify('first day of this month')) {
                $selectedMonth = $today->modify('first day of this month');
                $filterValue = $selectedMonth->format('Y-m');
            }
            $startDate = $selectedMonth->modify('first day of this month');
            $endDate = $startDate->modify('first day of next month')->modify('-1 second');
        } elseif ($filterType === 'annual') {
            $filterValue = $this->filterValue($filterType);
            $currentYear = (int) $today->format('Y');
            $selectedYear = filter_var($filterValue, FILTER_VALIDATE_INT);
            if ($selectedYear === false || $selectedYear < 1970 || $selectedYear > $currentYear || (string) $selectedYear !== $filterValue) {
                $selectedYear = $currentYear;
                $filterValue = (string) $currentYear;
            }
            $startDate = new \DateTimeImmutable($selectedYear . '-01-01');
            $endDate = $startDate->modify('+1 year')->modify('-1 second');
        } else {
            $filterValue = $today->format('Y-m-d');
            $startDate = $today;
            $endDate = $today->setTime(23, 59, 59);
        }

        $start = $startDate->getTimestamp();
        $end = $endDate->getTimestamp();
        $visits = Visit::find()
            ->with(['visitor', 'host'])
            ->where(['between', 'created_at', $start, $end])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return [$visits, $startDate, $endDate, $filterType, $filterValue];
    }

    private function filterValue(string $filterType): string
    {
        return match ($filterType) {
            'weekly' => (string) Yii::$app->request->get('date', ''),
            'monthly' => (string) Yii::$app->request->get('month', ''),
            'annual' => (string) Yii::$app->request->get('year', ''),
            default => date('Y-m-d'),
        };
    }
}
