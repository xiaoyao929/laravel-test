<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

abstract class BaseCommand extends Command
{
    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     * @param  string  $style
     * @param  int|string|null  $verbosity
     * @return void
     */
    public function line($string, $style = null, $verbosity = null)
    {
        $string = date('Y-m-d H:i:s') . "\t" . $string;
        return parent::line($string, $style, $verbosity);
    }
}
