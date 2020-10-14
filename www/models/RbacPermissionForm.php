<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Rbac权限表单
 * Class RbacPermissionForm
 * @package app\models
 *
 * @property string $parent 上级权限名称
 * @property string $name 权限名称
 * @property string $description 权限说明
 */
class RbacPermissionForm extends Model
{
    public $parent;
    public $name;
    public $description;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'required'],
            ['parent', 'safe'],
            ['name', function () {
                $authManager = Yii::$app->authManager;
                if (!empty($authManager->getPermission($this->name))) {
                    $this->addError('name', '已经存在这个权限了。');
                    return false;
                }
                return true;
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'parent' => '上级权限名称',
            'name' => '权限名称',
            'description' => '权限说明',
        ];
    }

    /**
     * 保存新权限
     * @return boolean
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $authManager = Yii::$app->authManager;
        $permission = $authManager->createPermission($this->name);
        $permission->description = $this->description;
        if (!$authManager->add($permission)) {
            $this->addError('name', '无法保存权限信息。');
            return false;
        }
        if (!empty($this->parent)) {
            $parent = $authManager->getPermission($this->parent);
            if (empty($parent)) {
                $this->addError('parent', '没有找到上级权限。');
                return false;
            }
            if (!$authManager->addChild($parent, $permission)) {
                $this->addError('parent', '无法设定上下级关系。');
                return false;
            }
        } else {
            // 顶层权限
            $role = $authManager->getRole('manager_role_1');
            $authManager->addChild($role, $permission);
        }
        return true;
    }
}
