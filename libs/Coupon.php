<?php

class Coupon
{

    private $redis;
    private $data;
    private $couponIdCount = 'coupon_id_count'; //生成优惠券id计数器
    private $couponIdListKey = 'coupon_id_list'; //优惠券id列表key
    private $couponItemKeyPrefix = 'coupon_item_'; //优惠券码key前缀

    const STATUS_USED = 0; // 优惠券未使用

    public function __construct($data = [])
    {
        $this->redis = get_redis();
        $this->data = $data;
    }

    /**
     *  优惠券列表
     */
    public function index()
    {
        $list = $this->redis->lrange($this->couponIdListKey, 0, -1);
        $items = [];

        if (!empty($list)) {
            foreach ($list as $couponId) {
                $item = $this->redis->hgetall($this->couponItemKeyPrefix . $couponId);
                if ($item) {
                    array_push($items, $item);
                }
            }
        }

        json(200, '', $items);
    }

    /**
     * 添加优惠券
     */
    public function add()
    {

        if (empty($this->data)) {
            exit('param error');
        }

        $created_time = date('Y-m-d',time());
        $status = self::STATUS_USED;

        //生成优惠券id
        $couponId = $this->redis->incr($this->couponIdCount);
        //添加优惠券id列表
        $this->redis->lpush($this->couponIdListKey, $couponId);

        $itemData = [
            'id' => $couponId,
            'title' => $this->data['title'],
            'num' => $this->data['num'],
            'price' => $this->data['price'],
            'expire_at' => $this->data['expire_at'],
            'created_time' => $created_time,
            'status' => $status,
        ];

        //添加到item
        $this->redis->hmset($this->couponItemKeyPrefix . $couponId, $itemData);

        //生成优惠券code
        $codes = $this->_generateCouponCode($this->data['num']);
        $this->redis->lpush($this->couponItemKeyPrefix . $couponId . '_codes', $codes);

        json(200, '添加成功');
    }

    /**
     * @param int $no_of_codes 生成优惠券code数量
     * @param int $code_length 优惠券code的长度
     * @return array
     */
    private function _generateCouponCode($no_of_codes, $code_length = 6)
    {
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $promotion_codes = array(); //这个数组用来接收生成的优惠码

        for ($j = 0; $j < $no_of_codes; $j++) {
            $code = "";

            for ($i = 0; $i < $code_length; $i++) {
                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }

            if (!in_array($code, $promotion_codes)) {
                $promotion_codes[$j] = $code; //将优惠码赋值给数组
            } else {
                $j--;
            }
        }

        return $promotion_codes;
    }

    public function __call($method, $args)
    {
        exit('route not exists.');
    }
}
