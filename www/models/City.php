<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * 城市列表
 * Class City
 * @package app\models
 *
 * @property integer $id
 * @property string $code 地区编码
 * @property string $name 地区名称
 */
class City extends ActiveRecord
{
    /**
     * 根据地区编号获取城市
     * @param $code string 地区编号如371300
     * @return City
     */
    public static function findByCode($code)
    {
        /** @var City $city */
        $city = City::find()->where(['code' => $code])->one();
        return $city;
    }

    /**
     * 返回完整的地址
     * @param $code boolean 是否返回编码
     * @return array 当前地址的完整描述,比如当前地址为临沂市,返回['山东省', '临沂市']
     */
    public function address($code = false)
    {
        $result = [];
        if (preg_match('/\d\d0000/', $this->code)) { // 一级 省
            $result[0] = $code ? $this->code : $this->name;
        } elseif (preg_match('/\d\d\d\d00/', $this->code)) { // 二级 市
            $p = City::findByCode(substr($this->code, 0, 2) . '0000');
            if (!empty($p)) {
                $result[0] = $code ? $p->code : $p->name;
            }
            $result[1] = $code ? $this->code : $this->name;
        } else { // 三级 区
            $p = City::findByCode(substr($this->code, 0, 2) . '0000');
            if (!empty($p)) {
                $result[0] = $code ? $p->code : $p->name;
            }
            $c = City::findByCode(substr($this->code, 0, 4) . '00');
            if (!empty($c)) {
                $result[1] = $code ? $c->code : $c->name;
            }
            $result[2] = $code ? $this->code : $this->name;
        }
        return array_values(array_filter($result));
    }

    /**
     * 返回上下级关系对应好的数组
     * @param $level integer 级别 1 省 2 省市 3 省市区
     * @return array
     */
    public static function getMap($level = 3)
    {
        $city_map = [];
        foreach (City::find()->orderBy('code ASC')->each() as $city) {/** @var City $city */
            if (preg_match('/0000$/', $city->code)) { // 省
                $city_map[$city->code] = [
                    'name' => $city->name,
                    'c_list' => [],
                ];
            } elseif (preg_match('/00$/', $city->code)) { // 市
                $p_code = substr($city->code, 0, 2) . '0000';
                $city_map[$p_code]['c_list'][$city->code] = [
                    'name' => $city->name,
                    'a_list' => [],
                ];
            } else { // 区
                $p_code = substr($city->code, 0, 2) . '0000';
                $c_code = substr($city->code, 0, 4) . '00';
                if (!isset($city_map[$p_code]['c_list'][$c_code])) {
                    $c_code = $city->code;
                    $city_map[$p_code]['c_list'][$c_code] = [
                        'name' => $city->name,
                        'a_list' => [],
                    ];
                }
                $city_map[$p_code]['c_list'][$c_code]['a_list'][$city->code] = [
                    'name' => $city->name,
                ];
            }
        }
        // 处理没有二级或没有二三级的情况
        // 710000台湾省没有二三级
        // 442000东莞市没有三级
        foreach ($city_map as $p_code => $p) {
            if (empty($p['c_list'])) {
                $city_map[$p_code]['c_list'][$p_code] = [
                    'name' => $p['name'],
                    'a_list' => [
                        $p_code => [
                            'name' => $p['name'],
                        ],
                    ],
                ];
                continue;
            }
            foreach ($p['c_list'] as $c_code => $c) {
                if (empty($c['a_list'])) {
                    $city_map[$p_code]['c_list'][$c_code]['a_list'] = [
                        $c_code => [
                            'name' => $c['name'],
                        ],
                    ];
                    continue;
                }
            }
        }
        if ($level == 3) {
            return $city_map;
        }
        if ($level == 2) {
            foreach ($city_map as $p_code => $p) {
                foreach ($p['c_list'] as $c_code => $c) {
                    unset($city_map[$p_code]['c_list'][$c_code]['a_list']);
                }
            }
            return $city_map;
        }
        if ($level == 1) {
            foreach ($city_map as $p_code => $p) {
                unset($city_map[$p_code]['c_list']);
            }
            return $city_map;
        }
        return [];
    }
}
