<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2025 Teclib' and contributors.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

use function Safe\preg_match;
use function Safe\scandir;

/**
 * Update from 10.0.2 to 10.0.3
 *
 * @return bool
 **/
function update1002to1003()
{
    /**
     * @var \DBmysql $DB
     * @var \Migration $migration
     */
    global $DB, $migration;

    $updateresult       = true;
    $ADDTODISPLAYPREF   = [];
    $DELFROMDISPLAYPREF = [];
    $update_dir = __DIR__ . '/update_10.0.2_to_10.0.3/';

    $migration->setVersion('10.0.3');

    $update_scripts = scandir($update_dir);
    foreach ($update_scripts as $update_script) {
        if (preg_match('/\.php$/', $update_script) !== 1) {
            continue;
        }
        require $update_dir . $update_script;
    }

    // ************ Keep it at the end **************
    foreach ($ADDTODISPLAYPREF as $type => $tab) { // @phpstan-ignore foreach.emptyArray (populated from child files)
        $rank = 1;
        foreach ($tab as $newval) {
            $DB->updateOrInsert(
                "glpi_displaypreferences",
                [
                    'rank'      => $rank++,
                ],
                [
                    'users_id'  => "0",
                    'itemtype'  => $type,
                    'num'       => $newval,
                ]
            );
        }
    }
    foreach ($DELFROMDISPLAYPREF as $type => $tab) { // @phpstan-ignore foreach.emptyArray (populated from child files)
        $DB->delete(
            'glpi_displaypreferences',
            [
                'itemtype'  => $type,
                'num'       => $tab,
            ]
        );
    }

    $migration->executeMigration();

    return $updateresult;
}
