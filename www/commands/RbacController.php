<?php

namespace app\commands;

use app\models\Manager;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * 权限初始化
 * Class RbacController
 * @package app\commands
 */
class RbacController extends Controller
{
    /**
     * 权限初始化
     * @throws \Exception
     */
    public function actionInit()
    {
        $this->stdout("此脚本将初始化RBAC权限系统，\n", Console::FG_RED);
        $this->stdout("新创建的权限将被删除，\n", Console::FG_RED);
        $this->stdout("初始化后需要系统管理员到后台重新给角色分配权限，\n", Console::FG_RED);
        if (!Console::confirm("请确认：")) {
            return;
        }

        $auth = Yii::$app->authManager;

        $this->stdout("删除全部权限分配。\n", Console::FG_RED);
        $auth->removeAllAssignments();
        $this->stdout("删除全部权限项。\n", Console::FG_RED);
        $auth->removeAll();

        $this->stdout("初始化RBAC权限数据。\n", Console::FG_YELLOW);
        $rbac  = $this->_initRbac();

        $this->stdout("创建系统管理员角色：", Console::FG_YELLOW);
        $user_admin = $auth->createRole('manager_role_1');
        $user_admin->description = '系统管理员';
        $auth->add($user_admin);
        $auth->addChild($user_admin, $rbac);
        $this->stdout("完成。\n", Console::FG_GREEN);

        foreach (Manager::find()
                     ->andWhere(['status' => Manager::STATUS_ACTIVE, 'rid' => 1])
                     ->each() as $admin) {
            $this->stdout("给管理员[{$admin->id}]分配系统管理员角色权限：", Console::FG_GREEN);
            $role = $auth->getRole('manager_role_1');
            $auth->assign($role, $admin->id);
            $this->stdout("完成。\n", Console::FG_GREEN);
        }
        $this->stdout("权限初始化完成。\n", Console::FG_GREEN);
    }

    /**
     * 权限相关
     * @throws \Exception
     */
    private function _initRbac()
    {
        $auth = Yii::$app->authManager;

        $this->stdout("创建权限 rbac/menu:", Console::FG_YELLOW);
        $rbac_menu = $auth->createPermission('rbac/menu');
        $rbac_menu->description = '权限管理菜单';
        $auth->add($rbac_menu);
        $this->stdout("完成。\n", Console::FG_GREEN);

        $this->stdout("创建权限 rbac/manager:", Console::FG_YELLOW);
        $rbac_manager = $auth->createPermission('rbac/manager');
        $rbac_manager->description = '管理员管理';
        $auth->add($rbac_manager);
        $auth->addChild($rbac_manager, $rbac_menu);
        $this->stdout("完成。\n", Console::FG_GREEN);

        $this->stdout("创建权限 rbac/role:", Console::FG_YELLOW);
        $rbac_role = $auth->createPermission('rbac/role');
        $rbac_role->description = '角色管理';
        $auth->add($rbac_role);
        $auth->addChild($rbac_role, $rbac_menu);
        $this->stdout("完成。\n", Console::FG_GREEN);

        $this->stdout("创建权限 rbac/item:", Console::FG_YELLOW);
        $rbac_item = $auth->createPermission('rbac/item');
        $rbac_item->description = '权限管理';
        $auth->add($rbac_item);
        $auth->addChild($rbac_item, $rbac_menu);
        $this->stdout("完成。\n", Console::FG_GREEN);

        $this->stdout("创建权限 rbac/log:", Console::FG_YELLOW);
        $rbac_log = $auth->createPermission('rbac/log');
        $rbac_log->description = '管理日志';
        $auth->add($rbac_log);
        $auth->addChild($rbac_log, $rbac_menu);
        $this->stdout("完成。\n", Console::FG_GREEN);

        $this->stdout("创建权限 rbac:", Console::FG_YELLOW);
        $rbac = $auth->createPermission('rbac');
        $rbac->description = '权限管理';
        $auth->add($rbac);
        $auth->addChild($rbac, $rbac_manager);
        $auth->addChild($rbac, $rbac_role);
        $auth->addChild($rbac, $rbac_item);
        $auth->addChild($rbac, $rbac_log);
        $this->stdout("完成。\n", Console::FG_GREEN);

        return $rbac;
    }
}
