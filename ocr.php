<?php

require __DIR__ . '/vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

class subtitle_ocr {

    public $src = 'img';
    public $dst = 'img_ocr';

    public function ocr_image($src, $dst) {
        // config
        $width = 1280;
        $height = 720;
        $bottom = 640;
        $threshold = 0.93;

        // load
        $imagick = new \Imagick($this->src . '/' . $src);
        // crop
        $imagick->cropImage($width, ($height - $bottom), 0, $bottom);
        // threshold
        $imagick->thresholdimage($threshold * \Imagick::getQuantum());
        // save
        $imagick->writeImage('tmp.jpg');

        // ocr
        try {
            $text = $this->filter_chars((new TesseractOCR('tmp.jpg'))->psm(1)->lang('chi_tra')->run());
        } catch (Exception $e) {
            echo(sprintf('Processing %s error!', $src) . PHP_EOL);
//            var_dump($e);
            $text = '';
        }


        $hit = true;
        if ($text == '') {
            $text = '000_unsuccessful_' . time();
            $hit = false;
        }

        // copy
        $dst_file = $dst . '/' . $text . '.jpg';
        echo($this->src . '/' . $src . ' -> ' . $dst_file . PHP_EOL);
        copy($this->src . '/' . $src, $dst_file);

        // move original file
        $mv_src = $this->src . '/' . $src;
        if ($hit) {
            $mv_dst = 'img_hit/' . $src;
        } else {
            $mv_dst = 'img_miss/' . $src;
        }
        rename($mv_src, $mv_dst);

    }

    public function filter_chars($text) {
        $filtered = '';
        for ($i = 0; $i < mb_strlen($text); $i++) {
            $check = false;
            $part = mb_substr($text, $i, 1);
            if (preg_match('/\p{N}/u', $part)) {
                $check = true;
            }
            if (preg_match('/\p{Han}/u', $part)) {
                $check = true;
            }
            if (preg_match('/\p{Latin}/u', $part)) {
                $check = true;
            }
            if (preg_match('/ /u', $part)) {
                $check = true;
            }
            if ($check) {
                $filtered .= $part;
            }
        }

        return $filtered;
    }

    public function main() {

        $a = scandir($this->src);
        $files = [];
        foreach($a as $v) {
            if (($v <> '..') && ($v <> '.') && ($v <> '.gitkeep')) {
                $files[] = $v;
            }
        }

        foreach($files as $f) {
            $this->ocr_image($f, $this->dst);
        }

    }
}

$a = new subtitle_ocr();
$a->main();
