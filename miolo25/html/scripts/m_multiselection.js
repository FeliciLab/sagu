dojo.declare("Miolo.MultiSelection",[Miolo.MultiTextField2],
{
    add: function (n) {
       var list = miolo.getElementById(this.mtfName + this.emptyField);
       console.log(list);
       var selection = miolo.getElementById(this.mtfName + '_options' + n);
       var n = list.length;
       var i = 0;
       var achou = false;
       var atext = selection.options[selection.selectedIndex].text;
       for (i = 0; i < n; i++) {
          if (list.options[i].text == atext)
             achou = true;
       }
       if (achou) {
           // FIXME: Checks another way to show messages do the user
           alert(miolo.i18n.ITEM_EXISTS_ON_LIST);
       }
       else {
          list.options[n] = new Option(atext);
          list.selectedIndex = n;
       }
    }
});
