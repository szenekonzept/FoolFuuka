<?php

namespace Foolz\Foolfuuka\Theme\Fuuka\Partial;

class ToolsSearch extends \Foolz\Theme\View
{
	public function toString()
	{
		$radix = $this->getBuilderParamManager()->getParam('radix');
		$search = $this->getBuilderParamManager()->getParam('search', []);

		if ( ! isset($radix) && \Preferences::get('foolfuuka.sphinx.global'))
		{
			// search can work also without a radix selected
			$search_radix = '_';
		}
		elseif (isset($radix))
		{
			$search_radix = $radix->shortname;
		}

		?>

		<?php if (isset($search_radix)) : ?>
		<div style="overflow:hidden;">
			<!--- Search Input -->
			<?php echo \Form::open(\Uri::create($search_radix.'/search')); ?>
			<div id="simple-search" class="postspan" style="float:left">
				<?= __('Text Search') ?>
				[<a class="tooltip" href="#">?<span>Place a <tt>|</tt> in between expressions to get one of them in results, e.g. <tt>tripcode|email</tt> to locate posts that contain either the word tripcode or email in them.<br />Place a <tt>-</tt> before a word to exclude posts containing the word: <tt>-tripcode</tt><br />Place quotes around phrases to find pages containing the phrase: <tt>"I am a filthy tripcode user"</tt></span></a>]

				<?php
				echo \Form::input([
					'name' => 'text',
					'id' => 'text',
					'size' => '24',
					'value' => (isset($search["text"])) ? rawurldecode($search["text"]) : ''
				]);
				?>

				<?php
				echo \Form::submit([
					'name' => 'submit',
					'value' => 'Go'
				]);
				?>
				<a href="<?php echo \Uri::create($search_radix.'/search') ?>" onclick="javascript:toggle('advanced-search');toggle('simple-search');return false;">[ <?= __('Advanced') ?> ]</a>
			</div>
			<?php echo \Form::close(); ?>

			<!--- Advanced Search Input -->
			<?php echo \Form::open(\Uri::create($search_radix.'/search')); ?>
			<div id="advanced-search" class="postspan" style="float:left;display:none">
				<table style="float:left">
					<tbody>
						<tr>
							<td colspan="2" class="theader"><?= __('Advanced Search') ?></td>
						</tr>
						<tr>
							<td class="postblock"><?= __('Text Search') ?></td>
							<td>
								<?php echo \Form::input(['name' => 'text', 'size' => '32', 'id' => 'text2', 'value' => (isset($search["text"])) ? rawurldecode($search["text"]) : '']); ?>
							</td>
						</tr>
						<tr>
							<td class="postblock"><?= __('Subject') ?></td>
							<td>
								<?php echo \Form::input(['name' => 'subject', 'size' => '32', 'id' => 'subject', 'value' => (isset($search["subject"])) ? rawurldecode($search["subject"]) : '']); ?>
							</td>
						</tr>
						<tr>
							<td class="postblock"><?= __('Username') ?> <a class="tooltip" href="#">[?]<span><?= __('Search for an <b>exact</b> username. Leave empty for any username.') ?></span></a></td>
							<td>
								<?php echo \Form::input(['name' => 'username', 'size' => '32', 'id' => 'username', 'value' => (isset($search["username"])) ? rawurldecode($search["username"]) : '']); ?>
							</td>
						</tr>
						<tr>
							<td class="postblock"><?= __('Tripcode') ?> <a class="tooltip" href="#">[?]<span><?= __('Search for an <b>exact</b> tripcode. Leave empty for any tripcode.') ?></span></a></td>
							<td>
								<?php echo \Form::input(['name' => 'tripcode', 'size' => '32', 'id' => 'tripcode', 'value' => (isset($search["tripcode"])) ? rawurldecode($search["tripcode"]) : '']); ?>
							</td>
						</tr>
						<tr>
							<td class="postblock"><?= __('E-mail') ?></td>
							<td>
								<?php echo \Form::input(['name' => 'email', 'size' => '32', 'id' => 'email', 'value' => (isset($search["email"])) ? rawurldecode($search["email"]) : '']); ?>
							</td>
						</tr>
						<tr>
							<td class="postblock"><?= __('From Date') ?> <a class="tooltip" href="#">[?]<span><?= __('Enter the starting date for your search.') ?><br/><?= __('Format: YYYY-MM-DD') ?></span></a></td>
							<td>
								<?php
								echo \Form::input(
									['type' => 'date',
										'name' => 'start',
										'placeholder' => 'YYYY-MM-DD',
										'id' => 'date_start',
										'value' => (isset($search["date_start"])) ? rawurldecode($search["date_start"]) : ''
									]
								);
								?>
							</td>
						</tr>
						<tr>
							<td class="postblock"><?= __('To Date') ?> <a class="tooltip" href="#">[?]<span><?= __('Enter the ending date for your search.') ?><br/><?= __('Format: YYYY-MM-DD') ?></span></a></td>
							<td>
								<?php
								echo \Form::input(
									[
										'type' => 'date',
										'name' => 'end',
										'id' => 'date_end',
										'placeholder' => 'YYYY-MM-DD',
										'value' => (isset($search["date_start"])) ? rawurldecode($search["date_start"]) : ''
									]
								);
								?>
							</td>
						</tr>
						<tr>
							<td class="postblock"><?= __('Filename') ?></td>
							<td>
								<?php echo \Form::input(['name' => 'filename', 'size' => '32', 'id' => 'filename', 'value' => (isset($search["filename"])) ? rawurldecode($search["filename"]) : '']); ?>
							</td>
						</tr>
						<tr>
							<td class="postblock"><?= __('Image Hash') ?></td>
							<td>
								<?php echo \Form::input(['name' => 'image', 'size' => '32', 'id' => 'image', 'value' => (isset($search["image"])) ? rawurldecode($search["image"]) : '']); ?>
							</td>
						</tr>

						<?php
							$checkboxes = [
								[
									'label' => __('Deleted posts'),
									'name' => 'deleted',
									'elements' => [
										['value' => false, 'text' => __('All')],
										['value' => 'deleted', 'text' => __('Only Deleted Posts')],
										['value' => 'not-deleted', 'text' => __('Only Non-Deleted Posts')]
									]
								],
								[
									'label' => __('Ghost posts'),
									'name' => 'ghost',
									'elements' => [
										['value' => false, 'text' => __('All')],
										['value' => 'only', 'text' => __('Only Ghost Posts')],
										['value' => 'none', 'text' => __('Only Non-Ghost Posts')]
									]
								],
								[
									'label' => __('Show posts'),
									'name' => 'filter',
									'elements' => [
										['value' => false, 'text' => __('All')],
										['value' => 'text', 'text' => __('Only Containing Images')],
										['value' => 'image', 'text' => __('Only Containing Text')]
									]
								],
								[
									'label' => __('Results'),
									'name' => 'type',
									'elements' => [
										['value' => false, 'text' => __('All')],
										['value' => 'op', 'text' => __('Only Opening Posts')],
										['value' => 'posts', 'text' => __('Only Reply Posts')]
									]
								],
								[
									'label' => __('Capcode'),
									'name' => 'capcode',
									'elements' => [
										['value' => false, 'text' => __('All')],
										['value' => 'user', 'text' => __('Only User Posts')],
										['value' => 'mod', 'text' => __('Only Moderator Posts')],
										['value' => 'admin', 'text' => __('Only Admin Posts')],
										['value' => 'dev', 'text' => __('Only Developer Posts')]
									]
								],
								[
									'label' => __('Order'),
									'name' => 'order',
									'elements' => [
										['value' => false, 'text' => __('New Posts First')],
										['value' => 'asc', 'text' => __('Old Posts First')]
									]
								]
							];

							foreach ($checkboxes as $checkbox) :
						?>
						<tr>
							<td class="postblock"><?= e($checkbox['label']) ?></td>
							<td>
						<?php foreach ($checkbox['elements'] as $element) : ?>
								<label>
								<?= \Form::radio($checkbox['name'], $element['value'] ? : '', isset($search[$checkbox['name']]) && $element['value'] === $search[$checkbox['name']]) ?>
								<span><?= e($element['text']) ?></span>
								</label><br />
						<?php endforeach; ?>
							</td>
						</tr>
						<?php endforeach; ?>

						<tr>
							<td class="postblock"><?= __('Action') ?></td>
							<td>
								<?php
								echo \Form::submit([
									'value' => 'Search',
									'name' => 'submit_search'
								]);

								if (\Preferences::get('foolfuuka.sphinx.global')) :
									echo \Form::submit([
										'value' => 'Global Search',
										'name' => 'submit_search_global'
									]);
								endif;
								?>
								<a href="#" onclick="javascript:toggle('advanced-search');toggle('simple-search');return false;">[ <?=__('Simple') ?> ]</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php echo \Form::close(); ?>

		<?php if (\Radix::getSelected()) : ?>
			<!--- Post Input -->
			<?php echo \Form::open(\Radix::getSelected()->shortname . '/post'); ?>
			<div class="postspan" style="float:left">
				<?= __('View Post') ?>

				<?php
				echo \Form::input([
					'name' => 'post',
					'id' => 'post',
					'size' => '9'
				]);
				?>

				<?php
				echo \Form::submit([
					'name' => 'submit',
					'value' => 'View',
					'onclick' => 'getPost(this.form); return false;'
				]);
				?>
			</div>
			<?php echo \Form::close(); ?>

			<!--- Page Input -->
			<?php echo \Form::open(\Radix::getSelected()->shortname . '/page'); ?>
			<div class="postspan" style="float:left">
				<?= __('View Page') ?>

				<?php
				echo \Form::input([
					'name' => 'page',
					'id' => 'page',
					'size' => '6',
					'value' => ((isset($page)) ? $page : 1)
				]);
				?>

				<?php
				echo \Form::submit([
					'name' => 'submit',
					'value' => 'View',
					'onclick' => 'location.href=\'' . \Uri::create(\Radix::getSelected()->shortname . '/page/') . '\' + this.form.page.value + \'/\'; return false;'
				]);
				?>

				<a class="tooltip" href="#">[?]<span><?= __('In Ghost Mode, only threads that contain ghost posts will be listed.') ?></span></a>

				<input type="button" value="View in Ghost Mode" onclick="location.href='<?php echo \Uri::create(\Radix::getSelected()->shortname . '/ghost') ?>' + this.form.page.value + '/'; return false;" />
			</div>
			<?php echo \Form::close(); ?>
		<?php endif; ?>
		</div>
		<?php endif;
	}
}