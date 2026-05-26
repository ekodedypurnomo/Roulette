<?php

/**
 * This file is part of the Roulette package.
 *
 * (c) Eko Dedy Purnomo <eko.dedy.purnomo@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Registered framework tunels — checked in order until one matches.
 *
 * Each entry is a fully-qualified class name that implements TunelAbstract.
 * The first class whose check() returns true becomes the active tunel.
 *
 * Auto-detected (no manual wiring required):
 *   Laravel / Lumen → Laravel.php
 *   CodeIgniter 4   → CodeIgniter4.php
 *   CodeIgniter 3   → Codeigniter3.php
 *   Phalcon 3/4/5   → Phalcon.php
 *
 * Manual wiring (no global app helper available):
 *   Symfony → Symfony::fromConnection($dbalConnection)
 *   Standalone PDO → Standalone::fromPdo($pdo)
 */
return [
    'Laravel'       => \Roulette\Tunel\Laravel::class,
    'CodeIgniter4'  => \Roulette\Tunel\CodeIgniter4::class,
    'CodeIgniter3'  => \Roulette\Tunel\Codeigniter3::class,
    'Phalcon'       => \Roulette\Tunel\Phalcon::class,
];
