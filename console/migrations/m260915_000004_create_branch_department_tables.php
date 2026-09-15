<?php

declare(strict_types=1);

use yii\db\Migration;

class m260915_000004_create_branch_department_tables extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%branches}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(64)->notNull()->unique(),
            'name' => $this->string(255)->notNull()->unique(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->null(),
        ]);
        $this->createTable('{{%departments}}', [
            'id' => $this->primaryKey(),
            'branch_id' => $this->integer()->notNull(),
            'code' => $this->string(100)->notNull(),
            'name' => $this->string(255)->notNull(),
            'created_at' => $this->integer()->null(),
        ]);
        $this->createIndex('uq-departments-branch-code', '{{%departments}}', ['branch_id', 'code'], true);
        $this->addForeignKey('fk-departments-branch', '{{%departments}}', 'branch_id', '{{%branches}}', 'id', 'CASCADE', 'CASCADE');

        $branches = [
            ['pbz-head-office', 'PBZ Head Office - Zanzibar'],
            ['pbz-mwanakwerekwe', 'PBZ Mwanakwerekwe Branch'],
            ['pbz-malindi', 'PBZ Malindi Branch'],
            ['pbz-chakechake', 'PBZ Chake Chake Branch'],
            ['pbz-wete', 'PBZ Wete Branch'],
            ['pbz-nungwi', 'PBZ Nungwi Branch'],
        ];
        foreach ($branches as [$code, $name]) {
            $this->insert('{{%branches}}', ['code' => $code, 'name' => $name, 'created_at' => time()]);
        }

        $departments = [
            'pbz-head-office' => ['customer-service' => 'Customer Service', 'accounts' => 'Accounts', 'loans' => 'Loans', 'human-resources' => 'Human Resources', 'ict' => 'ICT', 'management' => 'Branch Management'],
            'pbz-mwanakwerekwe' => ['customer-service' => 'Customer Service', 'accounts' => 'Accounts', 'loans' => 'Loans', 'operations' => 'Operations'],
            'pbz-malindi' => ['customer-service' => 'Customer Service', 'accounts' => 'Accounts', 'loans' => 'Loans', 'operations' => 'Operations'],
            'pbz-chakechake' => ['customer-service' => 'Customer Service', 'accounts' => 'Accounts', 'loans' => 'Loans'],
            'pbz-wete' => ['customer-service' => 'Customer Service', 'accounts' => 'Accounts', 'operations' => 'Operations'],
            'pbz-nungwi' => ['customer-service' => 'Customer Service', 'accounts' => 'Accounts', 'operations' => 'Operations'],
        ];
        foreach ($departments as $branchCode => $items) {
            $branchId = (int) $this->db->getLastInsertID();
            $branchId = (int) (new \yii\db\Query())->select('id')->from('{{%branches}}')->where(['code' => $branchCode])->scalar();
            foreach ($items as $code => $name) {
                $this->insert('{{%departments}}', ['branch_id' => $branchId, 'code' => $code, 'name' => $name, 'created_at' => time()]);
            }
        }
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-departments-branch', '{{%departments}}');
        $this->dropTable('{{%departments}}');
        $this->dropTable('{{%branches}}');
    }
}
