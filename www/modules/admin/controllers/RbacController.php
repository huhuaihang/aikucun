<?php

namespace app\modules\admin\controllers;

use app\models\Manager;
use app\models\ManagerLog;
use app\models\ManagerRole;
use app\models\RbacPermissionForm;
use Yii;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * 权限管理
 * Class RbacController
 * @package app\modules\admin\controllers
 */
class RbacController extends BaseController
{
    /**
     * 管理员管理
     * @throws ForbiddenHttpException
     * @return string
     */
    public function actionManager()
    {
        if (!$this->manager->can('rbac/manager')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = Manager::find();
        $query->andWhere(['>', 'id', 1])->andWhere(['<>', 'status', Manager::STATUS_DELETED]);
        $query->andFilterWhere(['like', 'username', $this->get('search_username')]);
        $query->andFilterWhere(['like', 'nickname', $this->get('search_nickname')]);
        $query->andFilterWhere(['like', 'mobile', $this->get('search_mobile')]);
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('create_time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('manager', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 添加/修改管理员
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return string
     * @throws \yii\base\Exception
     * @throws \Exception
     */
    public function actionEditManager()
    {
        if (!$this->manager->can('rbac/manager')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if ($this->isPost()) {
            $id = isset($this->post('Manager')['id']) ? $this->post('Manager')['id'] : 0;
        }
        if ($id > 0) {
            $model = Manager::findOne($id);
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到管理员信息。');
            }
            if ($model->rid == 1) {
                throw new ForbiddenHttpException('不能修改系统管理员。');
            }
        } else {
            $model = new Manager();
            $model->create_time = time();
        }
        if ($model->load($this->post())) {
            if (!empty($this->post('Manager')) && !empty($this->post('Manager')['password'])) {
                $model->password = Yii::$app->security->generatePasswordHash($this->post('Manager')['password']);
            }
            if ($model->isNewRecord && empty($model->password)) {
                $model->addError('password', '密码不能为空。');
            } else {
                if ($model->save()) {
                    $authManager = Yii::$app->authManager;
                    if (isset($model->oldAttributes) && !empty($model->oldAttributes['rid'])) {
                        $old_role = $authManager->getRole('manager_role_' . $model->oldAttributes['rid']);
                        if (!empty($old_role)) {
                            $authManager->revoke($old_role, $model->id);
                        }
                    }
                    $role = $authManager->getRole('manager_role_' . $model->rid);
                    if (!empty($role)) {
                        $authManager->assign($role, $model->id);
                    }
                    ManagerLog::info($this->manager->id, '保存管理员', print_r($model->attributes, true));
                    Yii::$app->session->addFlash('success', '数据已保存。');
                    Yii::$app->session->setFlash('redirect', json_encode([
                        'url' => Url::to(['/admin/rbac/manager']),
                        'txt' => '管理员列表'
                    ]));
                }
            }
        }
        return $this->render('manager_edit', [
            'model' => $model
        ]);
    }

    /**
     * 删除管理员AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionDeleteManager()
    {
        if (!$this->manager->can('rbac/manager')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = Manager::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到管理员。');
        }
        if ($model->rid == 1) {
            throw new ForbiddenHttpException('不能删除系统管理员。');
        }
        $model->status = Manager::STATUS_DELETED;
        if ($model->save()) {
            ManagerLog::info($this->manager->id, '删除管理员', print_r($model->attributes, true));
            return ['result' => 'success'];
        }
        return ['message' => '删除失败。', 'errors' => $model->errors];
    }

    /**
     * 切换管理员状态AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionSetStatus()
    {
        if (!$this->manager->can('rbac/manager')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = Manager::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到管理员。');
        }
        if ($model->rid == 1) {
            throw new ForbiddenHttpException('不能设置系统管理员。');
        }
        $model->status = [Manager::STATUS_ACTIVE => Manager::STATUS_STOPED, Manager::STATUS_STOPED => Manager::STATUS_ACTIVE][$model->status];
        if ($model->save()) {
            ManagerLog::info($this->manager->id, '修改管理员状态',  $model->id . ':' . $model->status);
            return ['result' => 'success'];
        } else {
            return ['message' => '操作失败。', 'errors' => $model->errors];
        }
    }

    /**
     * 角色管理
     * @throws ForbiddenHttpException
     * @return string
     */
    public function actionRole()
    {
        if (!$this->manager->can('rbac/role')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ManagerRole::find()->where(['>', 'id', 1])->andWhere(['status'=>ManagerRole::STATUS_OK]);
        $model_list = $query->all();
        return $this->render('role', [
            'model_list'=>$model_list
        ]);
    }

    /**
     * 添加/修改角色
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return string
     * @throws \Exception
     */
    public function actionEditRole()
    {
        if (!$this->manager->can('rbac/role')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        if ($this->isPost()) {
            $id = isset($this->post('ManagerRole')['id']) ? $this->post('ManagerRole')['id'] : 0;
        }
        if ($id > 0) {
            $model = ManagerRole::findOne($id);
            if (empty($model)) {
                throw new NotFoundHttpException('没有找到管理角色。');
            }
            if ($model->id == 1) {
                throw new ForbiddenHttpException('不能修改系统管理员角色。');
            }
        } else {
            $model = new ManagerRole();
            $model->status = ManagerRole::STATUS_OK;
        }
        if ($model->load($this->post()) && $model->save()) {
            // 保存权限 开始
            $auth_list = $this->post('auth');
            $authManager = Yii::$app->authManager;
            $auth_role = $authManager->getRole('manager_role_' . $model->id);
            if (!empty($auth_role)) {
                $authManager->remove($auth_role);
            }
            $auth_role = $authManager->createRole('manager_role_' . $model->id);
            $auth_role->description = $model->name . ':' . $model->remark;
            $authManager->add($auth_role);
            if (!empty($auth_list) && is_array($auth_list)) {
                foreach ($auth_list as $item_name) {
                    $auth_item = $authManager->getPermission($item_name);
                    if (!empty($auth_item)) {
                        $authManager->addChild($auth_role, $auth_item);
                    }
                }
            }
            foreach (Manager::find()->where(['rid' => $model->id])->each() as $manager) {
                $authManager->assign($auth_role, $manager->id);
            }
            // 保存权限 结束
            ManagerLog::info($this->manager->id, '保存角色', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
        }
        return $this->render('role_edit', [
            'model' => $model
        ]);
    }

    /**
     * 删除角色AJAX接口
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return array
     */
    public function actionDeleteRole()
    {
        if (!$this->manager->can('rbac/role')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $id = $this->get('id');
        $model = ManagerRole::findOne($id);
        if (empty($model)) {
            throw new NotFoundHttpException('没有找到管理员角色。');
        }
        if ($model->id == 1) {
            throw new ForbiddenHttpException('不能删除系统管理员角色。');
        }
        $model->status = ManagerRole::STATUS_DEL;
        $model->save();
        $authManager = Yii::$app->authManager;
        $auth_item = $authManager->getRole('manager_role_' . $model->id);
        if (!empty($auth_item)) {
            $authManager->remove($auth_item);
        }
        ManagerLog::info($this->manager->id, '删除角色', print_r($model->attributes, true));
        return ['result' => 'success'];
    }

    /**
     * 管理员日志
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @return string
     */
    public function actionLog()
    {
        if (!$this->manager->can('rbac/log')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $query = ManagerLog::find();
        $query->andFilterWhere(['mid' => $this->get('search_mid')]);
        if (!empty($this->get('search_username'))) {
            /** @var $search_manager Manager */
            $search_manager = Manager::find()->where(['like', 'username', $this->get('search_username')])->one();
            if (empty($search_manager)) {
                throw new NotFoundHttpException('没有找到管理用户：' . $this->get('search_username'));
            }
            $query->andWhere(['mid' => $search_manager->id]);
        }
        $query->andFilterWhere(['like', 'content', $this->get('search_content')]);
        $search_start_date = $this->get('search_start_date');
        if (!empty($search_start_date)) {
            $query->andFilterWhere(['>=', 'time', strtotime($search_start_date)]);
        }
        $search_end_date = $this->get('search_end_date');
        if (!empty($search_end_date)) {
            $query->andFilterWhere(['<', 'time', strtotime($search_end_date) + 86400]);
        }
        $pagination = new Pagination(['totalCount' => $query->count()]);
        $model_list = $query->orderBy('time DESC')->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('log', [
            'model_list' => $model_list,
            'pagination' => $pagination
        ]);
    }

    /**
     * 获取日志详情AJAX接口
     * @return array
     */
    public function actionLogDetail()
    {
        if (!$this->manager->can('rbac/log')) {
            return ['message' => '没有权限。'];
        }
        $id = $this->get('id');
        $log = ManagerLog::findOne($id);
        if (empty($log)) {
            return ['message' => '没有找到日志信息。'];
        }
        return [
            'result' => 'success',
            'log' => [
                'id' => $log->id,
                'content' => $log->content,
                'data' => $log->data,
            ],
        ];
    }

    /**
     * 权限列表
     * @throws ForbiddenHttpException
     * @return string
     */
    public function actionItem()
    {
        if (!$this->manager->can('rbac/item')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        return $this->render('item_list');
    }

    /**
     * 添加权限
     * @throws ForbiddenHttpException
     * @return string
     * @throws \Exception
     */
    public function actionEditItem()
    {
        if (!$this->manager->can('rbac/item')) {
            throw new ForbiddenHttpException('没有权限。');
        }
        $model = new RbacPermissionForm();
        $model->parent = $this->get('parent');
        if ($model->load($this->post()) && $model->save()) {
            ManagerLog::info($this->manager->id, '保存权限', print_r($model->attributes, true));
            Yii::$app->session->addFlash('success', '数据已保存。');
            Yii::$app->session->setFlash('redirect', json_encode([
                'url' => Url::to(['/admin/rbac/item']),
                'txt' => '权限列表'
            ]));
        }

        $parent_list = [];
        $authManager = Yii::$app->authManager;
        foreach ($authManager->getPermissions() as $permission) {
            if (strpos($permission->name, '/') !== false) {
                continue;
            }
            $parent_list[$permission->name] = $permission->description;
        }
        return $this->render('item_edit', [
            'model' => $model,
            'parent_list' => $parent_list,
        ]);
    }

    /**
     * 更新管理菜单AJAX接口
     * @return array
     * @throws \yii\base\Exception
     * @throws \Exception
     */
    public function actionUpdateMenu()
    {
        if (!$this->manager->can('rbac/item')) {
            return ['message' => '没有权限。'];
        }
        $permission_name = $this->get('permission_name');
        $authManager = Yii::$app->authManager;
        $permission = $authManager->getPermission($permission_name);
        if (empty($permission)) {
            return ['message' => '没有找到权限信息。'];
        }
        $menu_permission = $authManager->getPermission($permission_name . '/menu');
        if (empty($menu_permission)) {
            $menu_permission = $authManager->createPermission($permission_name . '/menu');
            $menu_permission->description = $permission->description . '菜单';
            $authManager->add($menu_permission);
        }
        $sub_permission_list = $authManager->getChildren($permission_name);
        foreach ($sub_permission_list as $sub_permission) {
            if ($authManager->hasChild($sub_permission, $menu_permission)) {
                continue;
            }
            $authManager->addChild($sub_permission, $menu_permission);
        }
        ManagerLog::info($this->manager->id, '更新权限管理菜单', $permission_name);
        return ['result' => 'success'];
    }
}
