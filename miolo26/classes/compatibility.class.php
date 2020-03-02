<?
class Business  extends MBusiness
{
    public function business($database = null, $data = null)
    {
        parent::__construct($database,$data);
    }
}

class Database        extends MDatabase {}
class sql             extends MSQL
{
    public function sql($columns = '', $tables = '', $where = '', $orderBy = '', $groupBy = '', $having = '')
    {
        parent::__construct($columns,$tables,$where,$orderBy,$groupBy,$having);
    }
}

class Handler         extends MHandler {}
class Auth            extends MAuth {}
class Login           extends MLogin {}
class Perms           extends MPerms {}
class Context         extends MContextOld {}
class Dump            extends MDump {}
class History         extends MHistory {}
class Log             extends MLog {}
class Profile         extends MProfile {}
class Service         extends MService {}
class Session         extends MSession {}
class Trace           extends MTrace {}
class SimpleXML       extends MSimpleXML {}
class Template        extends MTemplate {}
class Tree            extends MTree {}
class QueryRange      extends MQueryRange {}
class Util            extends MUtil {}
class InvertDate      extends MInvertDate {}
class FormatValue     extends MFormatValue {}
class QuotedPrintable extends MQuotedPrintable {}
class TreeArray       extends MTreeArray {}
class XMLTree         extends MXMLTree {} 
class Lookup          extends MLookup {}
class State           extends MState {}
class UI              extends MUI {}
class BasePainter     extends MBasePainter {}
class HTMLPainter     extends MHTMLPainter {}

class Report           extends MReport {}
class CrystalReport    extends MCrystalReport {}
class ezPDFReport      extends MEzPDFReport {}
class PDFReportColumn  extends MPDFReportColumn {}
class PDFReportControl extends MPDFReportControl {}
class PDFReport        extends MPDFReport {}

class ActionPanel extends MActionPanel {}
class Span extends MSpan {}
class Div extends MDiv {}
class Spacer extends MSpacer {}
class BoxTitle extends MBoxTitle {}
class Box extends MBox {}
class FormButton extends MButton {}
class InputButton extends MInputButton {}
class CheckBox extends MCheckBox {}
class RadioButton extends MRadioButton {}
class CompoundForm extends MCompoundForm {}
class Content extends MContent {}
class FileContent extends MFileContent {}
class Container extends MAreaContainer {}

class Form extends MForm
{
    public function form($title='',$action='',$close='',$icon='')
    {
        parent::__construct($title,$action,$close,$icon);
    }
}

class CSSForm extends MCSSForm {}
class CSSPForm extends MCSSPForm {}

class DataGrid2 extends MDataGrid2 {}
class DataGridColumn extends MDataGridColumn {}
class DataGridHyperLink extends MDataGridHyperLink {}
class DataGridControl extends MDataGridControl {}
class DataGridAction extends MDataGridAction {}

class Grid extends MGrid {}
class GridColumn extends MGridColumn {}
class GridHyperLink extends MGridHyperLink {}
class GridControl extends MGridControl {}
class GridAction extends MGridAction {}
class GridActionIcon extends MGridActionIcon {}
class GridActionText extends MGridActionText {}
class GridActionSelect extends MGridActionSelect {}
class GridFilter extends MGridFilter {}
class GridFilterText extends MGridFilterText {}
class GridFilterControl extends MGridFilterControl {}
class GridNavigator extends MGridNavigator {}

class ObjectGrid extends MObjectGrid {}
class ObjectGridColumn extends MObjectGridColumn {}
class ObjectGridHyperLink extends MObjectGridHyperLink {}
class ObjectGridControl extends MObjectGridControl {}
class ObjectGridAction extends MObjectGridAction {}

class BaseGroup extends MBaseGroup {}
class CheckBoxGroup extends MCheckBoxGroup {}
class RadioButtonGroup extends MRadioButtonGroup {}
class LinkButtonGroup extends MLinkButtonGroup {}
class ImageForm extends MImage {}
class ImageFormLabel extends MImageFormLabel {}
class IndexControl extends MIndexedControl {}
class IndexedForm extends MIndexedForm {}

class TextField extends MTextField 
{
    public function __construct( $label, $name, $value='', $size=10, $hint='', $validator=null )
    {
        parent::__construct( $name, $value='', $label, $size=10, $hint='' );
        //$name->addValidator ...
    }

}
class PasswordField extends MPasswordField {}
class HiddenField extends MHiddenField {}
class MultiLineField extends MMultiLineField {}
class FileField extends MFileField {}
class CalendarField extends MCalendarField {}
class CurrencyField extends MCurrencyField {}
class InputGrid extends MInputGrid {}
class PageComment extends MPageComment {}
class Separator extends MSeparator {}
class Label extends MLabel {}
class FieldLabel extends MFieldLabel {}
class TextHeader extends MTextHeader {}
class Text extends MText {}
class TextLabel extends MTextLabel {}
class HyperLink extends MLink {}
class LinkButton extends MLinkButton {}
class ActionHyperLink extends MActionHyperLink {}
class ImageLink extends MImageLink {}
class ImageLinkLabel extends MImageLinkLabel {}
class ImageButton extends MImageButton {}
class Selection extends MSelection {}
class MultiSelection extends MMultiSelection {}
class ComboBox extends MComboBox {}
class LookupField extends MLookupField {}
class LookupTextField extends MLookupTextField {}
class LookupGrid extends MLookupGrid {}
class Menu extends MMenu {}
class MultiSelectionField extends MMultiSelectionField {}
class MultiTextField2 extends MMultiTextField2 {}
class MultiTextField3 extends MMultiTextField3 {}
class OptionList extends MOptionList {}
class Option extends MOption {}
class OptionGroup extends MOptionGroup {}
class Panel extends MPanel {}
class Prompt extends MPrompt {}
class StatusBar extends MStatusBar {}
class TabbedForm extends MTabbedForm {}
class TabbedForm2 extends MTabbedForm2 {}
class SimpleTable extends MSimpleTable {}
class TableRaw extends MTableRaw {}
class TableXML extends MTableXML {}
class ThemeBox extends MThemeBox {}
class ThemeElement extends MThemeElement {}
class NavigationBar extends MNavigationBar {}
class UnOrderedList extends MUnOrderedList {}
class Validator extends MValidator {}
class RequiredValidator extends MRequiredValidator {}
class MASKValidator extends MMASKValidator {}
class EmailValidator extends MEmailValidator {}
class PasswordValidator extends MPasswordValidator {}
class CEPValidator extends MCEPValidator {}
class PHONEValidator extends MPHONEValidator {}
class TIMEValidator extends MTIMEValidator {}
class CPFValidator extends MCPFValidator {}
class CNPJValidator extends MCNPJValidator {}
class DATEDMYValidator extends MDATEDMYValidator {}
class DATEYMDValidator extends MDATEYMDValidator {}
class CompareValidator extends MCompareValidator {}
class RangeValidator extends MRangeValidator {}
class RegExpValidator extends MRegExpValidator {}
class IntegerValidator extends MIntegerValidator {}
class ButtonClose extends MButtonClose {}
class ButtonFind extends MButtonFind {}
class LinkBack extends MLinkBack {}
class OpenWindow extends MOpenWindow {}
class ButtonWindow extends MButtonWindow {}

?>