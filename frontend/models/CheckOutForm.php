<?php

declare(strict_types=1);

namespace frontend\models;

use common\models\Visit;
use yii\base\Model;

/**
 * Visitor check-out form (QR hash or name / national ID search).
 */
class CheckOutForm extends Model
{
    public string $qr_code_hash = '';
    public string $search = '';

    public function rules(): array
    {
        return [
            [['qr_code_hash', 'search'], 'trim'],
            [['qr_code_hash', 'search'], 'string', 'max' => 255],
            [
                ['qr_code_hash'],
                'required',
                'when' => static function (self $model): bool {
                    return $model->search === '';
                },
                'whenClient' => "function (attribute, value) {
                    return !$('#checkoutform-search').val();
                }",
                'message' => 'Enter a QR code or search by name / National ID.',
            ],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'qr_code_hash' => 'QR Code',
            'search' => 'Search (Name or National ID)',
        ];
    }

    /**
     * Find an active checked-in visit matching QR or search text.
     */
    public function findActiveVisit(): Visit|null
    {
        if (!$this->validate()) {
            return null;
        }

        $query = Visit::find()
            ->alias('v')
            ->joinWith(['visitor visitor'])
            ->andWhere([
                'v.status' => Visit::STATUS_CHECKED_IN,
                'v.check_out_time' => null,
            ]);

        if ($this->qr_code_hash !== '') {
            $query->andWhere(['v.qr_code_hash' => $this->qr_code_hash]);
        } else {
            $query->andWhere([
                'or',
                ['like', 'visitor.full_name', $this->search],
                ['visitor.national_id' => $this->search],
                ['visitor.phone_number' => $this->search],
            ]);
        }

        return $query->orderBy(['v.check_in_time' => SORT_DESC])->one();
    }
}
