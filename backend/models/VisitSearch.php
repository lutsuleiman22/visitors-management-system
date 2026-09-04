<?php

declare(strict_types=1);

namespace backend\models;

use common\models\Visit;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * VisitSearch represents the model behind the search form of Visit.
 */
class VisitSearch extends Visit
{
    public string|null $visitor_name = null;
    public string|null $visitor_phone = null;
    public string|null $host_username = null;
    public string $active_only = '0';

    public function rules(): array
    {
        return [
            [['id', 'visitor_id', 'host_user_id'], 'integer'],
            [['purpose', 'from_location', 'destination', 'qr_code_hash', 'visitor_pass_number', 'status', 'visitor_name', 'visitor_phone', 'host_username'], 'safe'],
            [['active_only'], 'in', 'range' => ['0', '1']],
        ];
    }

    public function scenarios(): array
    {
        return Model::scenarios();
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'visitor_name' => 'Visitor',
            'visitor_phone' => 'Phone',
            'host_username' => 'Host',
            'active_only' => 'Active Visits',
        ]);
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Visit::find()
            ->alias('v')
            ->joinWith(['visitor', 'host host']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'visitor_id',
                    'host_user_id',
                    'purpose',
                    'from_location',
                    'destination',
                    'visitor_pass_number',
                    'status',
                    'check_in_time',
                    'check_out_time',
                    'created_at',
                    'visitor_name' => [
                        'asc' => ['visitors.full_name' => SORT_ASC],
                        'desc' => ['visitors.full_name' => SORT_DESC],
                    ],
                    'visitor_phone' => [
                        'asc' => ['visitors.phone_number' => SORT_ASC],
                        'desc' => ['visitors.phone_number' => SORT_DESC],
                    ],
                    'host_username' => [
                        'asc' => ['host.username' => SORT_ASC],
                        'desc' => ['host.username' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'v.id' => $this->id,
            'v.visitor_id' => $this->visitor_id,
            'v.host_user_id' => $this->host_user_id,
            'v.status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'v.purpose', $this->purpose])
            ->andFilterWhere(['like', 'v.qr_code_hash', $this->qr_code_hash])
            ->andFilterWhere(['like', 'visitors.full_name', $this->visitor_name])
            ->andFilterWhere(['like', 'visitors.phone_number', $this->visitor_phone])
            ->andFilterWhere(['like', 'v.visitor_pass_number', $this->visitor_pass_number])
            ->andFilterWhere(['like', 'v.from_location', $this->from_location])
            ->andFilterWhere(['like', 'v.destination', $this->destination])
            ->andFilterWhere(['like', 'host.username', $this->host_username]);

        if ($this->active_only === '1') {
            $query->andWhere([
                'and',
                ['v.status' => Visit::STATUS_CHECKED_IN],
                ['v.check_out_time' => null],
            ]);
        }

        return $dataProvider;
    }
}
