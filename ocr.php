<?php

require __DIR__ . '/vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

class subtitle_ocr {

    public $src = 'img';
    public $dst = 'img_ocr';

    public $stat_hit = 0;
    public $stat_miss = 0;

    public $correct_find = [];
    public $correct_replace = [];

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
            $text = (new TesseractOCR('tmp.jpg'))->psm(1)->lang('chi_tra')->run();
        } catch (Exception $e) {
            echo(sprintf('Processing %s error!', $src) . PHP_EOL);
//            var_dump($e);
            $text = '';
        }

        if ($text <> '') {
            $text = $this->filter_chars($text);
            $text = str_replace($this->correct_find, $this->correct_replace, $text);
            $text = trim($text);
        }


        $hit = true;
        if ($text == '') {
            $text = '000_unsuccessful_' . time();
            $hit = false;
        }

        if ($hit) {
            $this->stat_hit++;
        } else {
            $this->stat_miss++;
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

        // load correct dic
        $h = fopen('correct.csv', 'r');
        while(($data = fgetcsv($h, 1000, ',')) !== false) {
            $this->correct_find[] = $data[0];
            $this->correct_replace[] = $data[1];
        }
        fclose($h);

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

        echo(sprintf('Hit: %d, Miss: %d, Rate: %0.2f%%', $this->stat_hit, $this->stat_miss, ($this->stat_hit/($this->stat_hit+$this->stat_miss)) * 100) . PHP_EOL);

    }
}

$a = new subtitle_ocr();
$a->main();
