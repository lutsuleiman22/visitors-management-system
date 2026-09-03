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
    public string|null $host_username = null;

    public function rules(): array
    {
        return [
            [['id', 'visitor_id', 'host_user_id'], 'integer'],
            [['purpose', 'qr_code_hash', 'status', 'visitor_name', 'host_username'], 'safe'],
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
            'host_username' => 'Host',
        ]);
    }

    public function search(array $params): ActiveDataProvider
    {
        $query = Visit::find()
            ->alias('v')
            ->joinWith(['visitor visitor', 'host host']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
                'attributes' => [
                    'id',
                    'visitor_id',
                    'host_user_id',
                    'purpose',
                    'status',
                    'check_in_time',
                    'check_out_time',
                    'created_at',
                    'visitor_name' => [
                        'asc' => ['visitor.full_name' => SORT_ASC],
                        'desc' => ['visitor.full_name' => SORT_DESC],
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
            ->andFilterWhere(['like', 'visitor.full_name', $this->visitor_name])
            ->andFilterWhere(['like', 'host.username', $this->host_username]);

        return $dataProvider;
    }
}
