<?php
/**
 * CodeIgniter Skeleton
 *
 * A ready-to-use CodeIgniter skeleton  with tons of new features
 * and a whole new concept of hooks (actions and filters) as well
 * as a ready-to-use and application-free theme and plugins system.
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2018, Kader Bouyakoub <bkader@mail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package 	CodeIgniter
 * @author 		Kader Bouyakoub <bkader@mail.com>
 * @copyright	Copyright (c) 2018, Kader Bouyakoub <bkader@mail.com>
 * @license 	http://opensource.org/licenses/MIT	MIT License
 * @link 		https://github.com/bkader
 * @since 		Version 1.0.0
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Language Module - Admin
 *
 * @package 	CodeIgniter
 * @subpackage 	Skeleton
 * @category 	Modules\Views
 * @author 		Kader Bouyakoub <bkader@mail.com>
 * @link 		https://github.com/bkader
 * @copyright 	Copyright (c) 2018, Kader Bouyakoub (https://github.com/bkader)
 * @since 		Version 1.0.0
 * @version 	1.3.0
 */
?><h2 class="page-header clearfix"><?php _e('manage_languages'); ?></h2>
<div class="panel panel-default">
	<div class="panel-body">
		<p class="text-muted"><?php _e('manage_languages_tip'); ?></p><br>
		<table class="table table-condensed">
			<tbody>
				<tr>
					<th><?php _e('language'); ?></th>
					<th><?php _e('abbreviation'); ?></th>
					<th><?php _e('folder'); ?></th>
					<th><?php _e('is_default'); ?></th>
					<th><?php _e('enabled'); ?></th>
					<th class="text-right"><?php _e('action'); ?></th>
				</tr>
		<?php foreach ($languages as $lang): ?>
				<tr>
					<?php if (isset($lang['available']) && false === $lang['available']): ?>
					<td><del title="<?php _e('missing_language_folder'); ?>" class="text-danger"><?php echo $lang['name_en']; ?>&nbsp;<small class="text-muted"><?php echo $lang['name']; ?></small></del></td>
					<?php else: ?>
					<td><?php echo $lang['name_en']; ?>&nbsp;<small class="text-muted"><?php echo $lang['name']; ?></small></td>
					<?php endif; ?>
					<td><?php echo $lang['code']; ?>&nbsp;<small class="text-muted"><?php echo $lang['locale']; ?></small></td>
					<td><?php echo $lang['folder']; ?></td>
					<td><?php echo label_condition($lang['folder'] === $language); ?></td>
					<td><?php echo label_condition(in_array($lang['folder'], $available_languages)); ?></td>
					<td class="text-right">
						<?php if ($lang['folder'] !== $language): ?>
						<!-- Make default action -->
						<?php echo safe_admin_anchor('language/make_default/'.$lang['folder'], lang('make_default'), 'class="btn btn-default btn-xs"'); ?>&nbsp;
						<?php endif; ?>
					<?php if ($lang['folder'] !== 'english'): ?>
						<?php if ( ! in_array($lang['folder'], $available_languages)): ?>
						<?php echo safe_admin_anchor('language/enable/'.$lang['folder'], lang('enable'), 'class="btn btn-success btn-xs"'); ?>&nbsp;
						<?php else: ?>
						<?php echo safe_admin_anchor('language/disable/'.$lang['folder'], lang('disable'), 'class="btn btn-danger btn-xs"'); ?>&nbsp;
						<?php endif; ?>
					<?php endif; ?>
					</td>
				</tr>
		<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
