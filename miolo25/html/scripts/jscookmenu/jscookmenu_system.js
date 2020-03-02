/*
 * ThemeGray by Ian Reyes and Heng Yuan
 */
// directory of where all the images are
var cmThemeSystemBase = '/images/';

var cmThemeSystem =
{
	prefix:	'ThemeSystem',
  	// main menu display attributes
  	//
  	// Note.  When the menu bar is horizontal,
  	// mainFolderLeft and mainFolderRight are
  	// put in <span></span>.  When the menu
  	// bar is vertical, they would be put in
  	// a separate TD cell.

  	// HTML code to the left of the folder item
  	mainFolderLeft: '',
  	// HTML code to the right of the folder item
  	mainFolderRight: '',
	// HTML code to the left of the regular item
	mainItemLeft: '',
	// HTML code to the right of the regular item
	mainItemRight: '',

	// sub menu display attributes

	// 0, HTML code to the left of the folder item
	folderLeft: '<img alt="" src="' + cmThemeSystemBase + 'spacer.gif">',
	// 1, HTML code to the right of the folder item
	folderRight: '<img alt="" src="' + cmThemeSystemBase + 'arrow.gif">',
	// 2, HTML code to the left of the regular item
	itemLeft: '<img alt="" src="' + cmThemeSystemBase + 'spacer.gif">',
	// 3, HTML code to the right of the regular item
	itemRight: '<img alt="" src="' + cmThemeSystemBase + 'blank.gif">',
	// 4, cell spacing for main menu
	mainSpacing: 0,
	// 5, cell spacing for sub menus
	subSpacing: 0,
/*
	// HTML code to the left of the folder item
	folderLeft: '>',
	// HTML code to the right of the folder item
	folderRight: '&lt;',
	// HTML code to the left of the regular item
	itemLeft: '&nbsp;',
	// HTML code to the right of the regular item
	itemRight: '&nbsp;',
	// cell spacing for main menu
	mainSpacing: 1,
	// cell spacing for sub menus
	subSpacing: 0,
	// auto dispear time for submenus in milli-seconds
	delay: 200
*/
	// rest use default settings
};

// for sub menu horizontal split
var cmThemeSystemHSplit = [_cmNoClick, '<td colspan="3" class="ThemeSystemMenuSplit"><div class="ThemeSystemSplit"></div></td>'];
// for vertical main menu horizontal split
var cmThemeSystemMainHSplit = [_cmNoClick, '<td colspan="3" class="ThemeSystemMenuSplit"><div class="ThemeSystemMenuSplit"></div></td>'];
// for horizontal main menu vertical split
var cmThemeSystemMainVSplit = [_cmNoClick, '|'];
