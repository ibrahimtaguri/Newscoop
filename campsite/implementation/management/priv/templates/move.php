<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/$ADMIN_DIR/templates/template_common.php");

$f_template_code = Input::Get('f_template_code', 'array', array(), true);
$f_destination_folder = Input::Get('f_destination_folder', 'string', '', true);
$f_current_folder = Input::Get('f_current_folder', 'string', 0, true);
$f_action = Input::Get('f_action');

$f_current_folder = urldecode($f_current_folder);
//
// Check permissions
//
if ($f_action == "move") {
	if (!$g_user->hasPermission("ManageTempl")) {
		camp_html_display_error(getGS("You do not have the right to move articles."));
		exit;
	}
}

// $articles array:
// The articles that were initially selected to perform the move or duplicate upon.
$templates = array();
for ($i = 0; $i < count($f_template_code); $i++) {
	$tmpTemplate =& new Template($f_template_code[$i]);
	$templates[] = $tmpTemplate;
}

if (!Input::IsValid()) {
	camp_html_display_error(getGS('Invalid input: $1', Input::GetErrorString()));
	exit;
}

// Get all the templates
$folders = array();
$folders = Template::GetAllFolders($folders);
$i = 1;
foreach ($folders as $folder) {
        $folders[$i++] = substr($folder, strlen($Campsite['TEMPLATE_DIRECTORY']));
}
$folders[0] = '/';

//
// This section is executed when the user finally hits the action button.
//
if (isset($_REQUEST["action_button"])) {
	if ($f_destination_folder != '/') {
		$url = "/$ADMIN/templates/index.php?Path=$f_destination_folder";
	} else {
		$url = "/$ADMIN/templates/index.php";
	}

	if ($f_action == "move") {
		// Move all the templates requested.
		foreach ($templates as $template) {
			if ($template->move($f_current_folder, $f_destination_folder)) {
				$replaceObj = new FileTextSearch();
				$replaceObj->setExtensions(array('tpl'));
				$replaceObj->setSearchKey(' '.$template->getName());
				$replaceObj->setReplacementKey(' '.ltrim($f_destination_folder
								  . '/'
								  . basename($template->getName()), '/'));
				$replaceObj->findReplace($Campsite['TEMPLATE_DIRECTORY']);
				Template::UpdateOnChange($template->getName(),
							 $f_destination_folder . '/' . basename($template->getName()));

			}
		}
		camp_html_add_msg(getGS("Template(s) moved."), "ok");
		camp_html_goto_page($url);

	}
} // END perform the action

$crumbs = array();
$crumbs[] = array(getGS("Configure"), "");
$crumbs[] = array(getGS("Templates"), "/$ADMIN/templates/");
$crumbs[] = array(getGS("Move templates"), "");
echo camp_html_breadcrumbs($crumbs);

?>

<?php camp_html_display_msgs(); ?>

<P>
<DIV class="page_title" style="padding-left: 18px;">
<?php p(putGS("These templates")); ?>:
</DIV>

<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="6" style="margin-left: 10px;">
<FORM NAME="move" METHOD="POST">
<INPUT type="hidden" name="f_action" value="<?php p($f_action); ?>">
<?php
if (!empty($f_current_folder)) {
?>
<INPUT type="hidden" name="f_current_folder" value="<?php p($f_current_folder); ?>">
<?php
}
foreach ($templates as $template) {
?>
<INPUT type="hidden" name="f_template_code[]" value="<?php p($template->getName()); ?>">
<?php
}
?>
<TR>
	<TD>
		<TABLE cellpadding="3">
		<?php
		$class = 0;
		foreach ($templates as $template) {
		?>
		<TR class="<?php if ($class) { ?>list_row_even<?php } else { ?>list_row_odd<?php } $class = !$class; ?>">
			<TD><?php p($template->getName()); ?></TD>
		</TR>
		<?php
		}
		?>
		</TABLE>
	</TD>
</TR>
</TABLE>
<P>
<DIV class="page_title" style="padding-left: 18px;">
<?php putGS("to folder"); ?>:
</DIV>
<P>
<TABLE BORDER="0" CELLSPACING="0" CELLPADDING="6" class="table_input" width="400px" style="margin-left: 18px;">
<TR>
	<TD align="left">
		<TABLE align="left" border="0" width="100%">
		<TR>
			<TD colspan="2" style="padding-left: 20px; padding-bottom: 5px;font-size: 12pt; font-weight: bold;"><?php  putGS("Select destination"); ?></TD>
		</TR>
		<TR>
			<TD>
				<TABLE border="0">
				<TR>
					<TD VALIGN="middle" ALIGN="RIGHT" style="padding-left: 20px;"><?php  putGS('Folder'); ?>: </TD>
					<TD valign="middle" ALIGN="LEFT">
						<?php if (count($folders) > 1) { ?>
						<SELECT NAME="f_destination_folder" class="input_select" ONCHANGE="if (this.options[this.selectedIndex].value != '<?php p($f_destination_folder); ?>') {this.form.submit();}">
						<OPTION VALUE=""><?php  putGS('---Select folder---'); ?></option>
						<?php
						foreach ($folders as $folder) {
							camp_html_select_option($folder, $f_destination_folder, $folder);
						}
						?>
						</SELECT>
						<?php } elseif (count($folders) == 1) {
							$tmpFolder = camp_array_peek($folders);
							p(htmlspecialchars($tmpFolder));
							?>
							<INPUT type="hidden" name="f_destination_folder" value="<?php p($folder); ?>">

						<?php } else { ?>
							<SELECT class="input_select" DISABLED><OPTION><?php  putGS('No folders'); ?></option></SELECT>
						<?php }	?>
					</TD>
				</TR>
				</TABLE>
			</TD>
		</TR>
		<TR>
			<TD colspan="2">
			<?php
				if ($f_current_folder == $f_destination_folder) {
					putGS("The destination folder is the same as the source folder."); echo "<BR>\n";
				}
			?>
			</TD>
		</TR>
		<TR>
			<TD align="center" colspan="2">
				<INPUT TYPE="submit" Name="action_button" Value="<?php p(putGS("Move templates")); ?>" <?php if ((empty($f_destination_folder)) || $f_current_folder == $f_destination_folder) { echo 'class="button_disabled"'; } else { echo "class=\"button\""; }?> />
			</TD>
		</TR>
		</TABLE>
	</TD>
</TR>
</FORM>
</TABLE>
<P>

<?php camp_html_copyright_notice(); ?>
