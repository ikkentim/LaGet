<?php namespace LaGet;

use \Request;

class RequestUtil
{
    public static function getUploadedFile($name)
    {
        preg_match('/boundary=(.*)$/', Request::header('Content-Type'), $boundary);

        if (!count($boundary))
            return false;

        $blocks = preg_split("/-+{$boundary[1]}/", file_get_contents('php://input'));
        array_pop($blocks); // Last block is empty

        foreach ($blocks as $block) {
            if (empty($block) || strpos($block, 'application/octet-stream') === false) {
                \Log::notice('upload::block is not application/octet-stream');
                continue;
            }
            preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $nameAndStream);

            $streamName = $nameAndStream[1];
            $stream = $nameAndStream[2];

            if ($name != $streamName || strlen($stream) <= 0) {
                \Log::notice('upload::block is not of name' . $name);
                continue;
            }
            $check = unpack('C*', substr($stream, -2));
            if ($check[1] == 13 && $check[2] == 10)
                $stream = substr($stream, 0, -2);

            $tmpPath = tempnam(sys_get_temp_dir(), '');
            file_put_contents($tmpPath, $stream);

            \Log::notice('upload::block stored to ' .  $tmpPath);
            return $tmpPath;
        }

        \Log::notice('upload::right block not found');
        return false;
    }
}