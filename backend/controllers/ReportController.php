<?php

declare(strict_types=1);

namespace backend\controllers;

use backend\components\BaseController;
use backend\models\ReportFilter;
use backend\services\ReportService;
use common\models\User;
use common\services\BranchCatalog;
use Yii;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

class ReportController extends BaseController
{
    public function actionIndex(): string
    {
        $this->requireRole(User::ROLE_ADMIN);
        $filter = new ReportFilter();
        $filter->load(Yii::$app->request->get());
        return $this->render('index', [
            'model' => $filter,
            'branches' => BranchCatalog::all(),
            'receptions' => $this->receptionsForBranch($filter->branch),
            'receptionsByBranch' => $this->receptionsByBranch(),
        ]);
    }

    public function actionPdf(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        try {
            if (!class_exists('Mpdf\\Mpdf')) {
                throw new ServerErrorHttpException('PDF export dependency is not installed.');
            }
            $filter = $this->filter();
            $pdf = new \Mpdf\Mpdf();
            $pdf->WriteHTML((new ReportService())->html($filter));
            return Yii::$app->response->sendContentAsFile($pdf->Output('', 'S'), 'visitor-report.pdf', ['mimeType' => 'application/pdf']);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'The PDF report is temporarily unavailable.');
            return $this->redirect(['index']);
        }
    }

    public function actionExcel(): Response
    {
        $this->requireRole(User::ROLE_ADMIN);
        try {
            if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                throw new ServerErrorHttpException('Excel export dependency is not installed.');
            }
            $filter = $this->filter();
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['Visitor', 'Phone', 'Host', 'Purpose', 'Origin', 'Pass', 'Status', 'Check-in', 'Check-out'];
            $sheet->fromArray($headers, null, 'A1');
            $rows = [];
            foreach ((new ReportService())->rows($filter) as $row) {
                $rows[] = array_map(static fn (string $field): string => (string) ($row[$field] ?? ''), [
                    'visitor_name', 'visitor_phone', 'host_name', 'purpose', 'from_location',
                    'visitor_pass_number', 'status', 'check_in_time', 'check_out_time',
                ]);
            }
            $sheet->fromArray($rows, null, 'A2');
            $sheet->getStyle('A1:I1')->getFont()->setBold(true);
            foreach (range('A', 'I') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            ob_start();
            $writer->save('php://output');
            return Yii::$app->response->sendContentAsFile((string) ob_get_clean(), 'visitor-report.xlsx', ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        } catch (\Throwable $exception) {
            if (ob_get_level() > 0) {
                ob_end_clean();
            }
            Yii::error($exception->getMessage(), __METHOD__);
            Yii::$app->session->setFlash('error', 'The Excel report is temporarily unavailable.');
            return $this->redirect(['index']);
        }
    }

    private function filter(): ReportFilter
    {
        $filter = new ReportFilter();
        $filter->load(Yii::$app->request->get());
        $filter->validate();
        return $filter;
    }

    /** @return array<int, User> */
    private function receptionsForBranch(string $branch): array
    {
        $query = User::find()->where(['role' => User::ROLE_RECEPTION, 'status' => User::STATUS_ACTIVE]);
        if ($branch !== '' && array_key_exists($branch, BranchCatalog::all())) {
            $query->andWhere(['branch_code' => $branch]);
        }
        return $query->orderBy(['username' => SORT_ASC])->all();
    }

    /** @return array<string, array<int, array{id: int, username: string}>> */
    private function receptionsByBranch(): array
    {
        $result = [];
        foreach (User::find()->where(['role' => User::ROLE_RECEPTION, 'status' => User::STATUS_ACTIVE])->orderBy(['username' => SORT_ASC])->asArray()->all() as $reception) {
            $result[(string) $reception['branch_code']][] = [
                'id' => (int) $reception['id'],
                'username' => (string) $reception['username'],
            ];
        }
        return $result;
    }
}
