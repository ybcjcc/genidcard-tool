#!/usr/bin/env php
<?php

class Gen
{
    protected $salt = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];

    protected $checksum = [1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2];

    protected function calcLastCode($code)
    {
        if (strlen($code) != 17) {
            throw new Exception("code length error");
        }

        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum += $code[$i] * $this->salt[$i];
        }
        $seek = $sum % 11;
        return (string) $this->checksum[$seek];
    }

    protected function genNum($cityCode, $birthday, $isMale = true)
    {
        if (strlen($cityCode) != 6) {
            throw new Exception("city code length != 6");
        }

        if (strlen($birthday) != 8) {
            throw new Exception("birthday length != 8");
        }

        // 男 倒数第二位是奇数，女 倒数第二是偶数
        $idx = rand(0, 499);
        if ($isMale) {
            $range = range(1, 999, 2);
            $rand = $range[$idx];
        } else {
            $range = range(0, 999, 2);
            $rand = $range[$idx];
        }

        $rand = str_pad($rand, 3, 0, STR_PAD_LEFT);

        $_17 = $cityCode . $birthday . $rand;

        return $_17 . $this->calcLastCode($_17);
    }

    protected function parseName($name)
    {
        $arr = self::getData();

        foreach ($arr as $item) {
            if (strpos($item->name, $name) !== false) {
                return $item->code;
            }
        }

        throw new Exception($name . "不存在");
    }

    public static function exec()
    {
        if (!function_exists('readline')) {
            function readline($prompt)
            {
                echo $prompt;
                $input = '';
                while (1) {
                    $key = fgetc(STDIN);
                    switch ($key) {
                        case "\n":
                            return $input;
                        default:
                            $input .= $key;
                    }
                }
            }
        }

        inputArea:
        $area = readline("请输入区县名称，如武侯区（默认 武侯区）>");
        $area = trim($area);
        if (strlen($area) == 0) {
            $area = "武侯区";
        }

        $obj = new self;
        try {
            $code = $obj->parseName($area);
        } catch (Exception $exception) {
            echo $exception->getMessage();
            goto inputArea;
        }

        inputBirthday:
        $birthDay = readline("请8位的出生年月日（默认19900101）>");
        if (strlen($birthDay) == 0) {
            $birthDay = '19900101';
        }

        $birthDay = trim($birthDay);
        if (strlen($birthDay) == 0) {
            echo $birthDay . "生日格式错误";
            goto inputBirthday;
        }

        inputSex:
        $isMale = readline("请输入性别 M-男 F-女，（默认M）>");
        $isMale = trim($isMale);
        $isMale = in_array($isMale, ['f', 'F']) ? false : true;

        inputBatch:
        $batch = readline("请输入批次数量（默认5）>");
        $batch = trim($batch);
        if (!$batch || $batch < 1) {
            $batch = 5;
        }

        echo "身份证号码列表：", "\n";
        for ($i = 0; $i < $batch; $i++) {
            echo $obj->genNum($code, $birthDay, $isMale), "\n";
        }
    }

    public static function getData()
    {
        return json_decode(gzuncompress(file_get_contents("areas.json.press")));
    }
}

Gen::exec();;