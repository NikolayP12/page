<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Page module version information
 *
 * @package    mod_page
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @copyright  2024 Nikolay <nikolaypn2002@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'mod_page';    // Full name of the plugin (used for diagnostics).
$plugin->release   = '1.4.0';       // Version release of the plugin.
$plugin->version   = 2023100900;    // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2023100400;    // Requires this Moodle version.
$plugin->cron      = 0;
