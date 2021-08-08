<?php
/* SPDX-License-Identifier: Zlib */
/* FSTube v1.2 (August 2021)
 * Copyright (C) 2020-2021 Norbert de Jonge <mail@norbertdejonge.nl>
 *
 * This software is provided 'as-is', without any express or implied
 * warranty.  In no event will the authors be held liable for any damages
 * arising from the use of this software.
 *
 * Permission is granted to anyone to use this software for any purpose,
 * including commercial applications, and to alter it and redistribute it
 * freely, subject to the following restrictions:
 *
 * 1. The origin of this software must not be misrepresented; you must not
 *    claim that you wrote the original software. If you use this software
 *    in a product, an acknowledgment in the product documentation would be
 *    appreciated but is not required.
 * 2. Altered source versions must be plainly marked as such, and must not be
 *    misrepresented as being the original software.
 * 3. This notice may not be removed or altered from any source distribution.
 */

include_once (dirname (__FILE__) . '/fst_base.php');
/*** Do NOT move VerifyCreate() here. ***/

$im = imagecreatetruecolor (180, 180);
$bg = imagecolorallocate ($im, 0xFF, 0xFF, 0xFF);
$fg = imagecolorallocate ($im, 0x00, 0x00, 0x00);
imagefill ($im, 0, 0, $bg);
/*** imagestring ($im, 5, 5, 5, VerifyShow(), $fg); ***/
$font = 'fonts/Vermi_di_Rouge.ttf';
imagettftext ($im, 24, 45, 36, 165, $fg, $font, VerifyShow());
header ('Cache-Control: no-cache, must-revalidate');
header ('Content-type: image/png');
imagepng ($im);
imagedestroy ($im);
?>
