function gridChk(chkRow, checkboxId) {
  var tr = document.getElementById('row' + checkboxId);
  if (chkRow.checked) {
    if (tr.className=='m-grid-row-1')
      tr.className='m-grid-row-1-checked';
    else if (tr.className=='m-grid-row-2')
      tr.className='m-grid-row-2-checked';
  }
  else {
    if (tr.className=='m-grid-row-1-checked')
      tr.className='m-grid-row-1';
    else if (tr.className=='m-grid-row-2-checked')
      tr.className='m-grid-row-2';
  }
}

function gridChkAll(chkAll, n, gridname) {
  for ( var i=0; i < n; i++ )
  {
	 var chkRow = document.getElementById('select'+ gridname +'[' + i + ']');
     if (chkAll.checked != chkRow.checked) {
       chkRow.checked = chkAll.checked;
       gridChk(chkRow, gridname +'[' + i + ']');
     }
  }
}

function gridChkEachRow(n, gridname) {
  for ( var i=0; i < n; i++ )
  {
     var chkRow = document.getElementById('select'+ gridname +'[' + i + ']');
     gridChk(chkRow, gridname +'[' + i + ']');
  }
}

function gridScroll( id2, id1 )
{
    var obj1 = document.getElementById(id1);
    var obj2 = document.getElementById(id2);
    obj1.scrollLeft = obj2.scrollLeft;
}

function gridSetScroll( id2, id1 )
{
    var obj1 = document.getElementById(id1);
    var obj2 = document.getElementById(id2);
    obj1.style.width = obj2.style.width; //scrollWidth;

    var table1 = document.getElementById( 't' + id1 + 'first');
    //table1.style.width = obj2.scrollWidth + 'px';
    var table2 = document.getElementById( 't' + id2 + 'first');

    var width = 0;
    for( var i=0; i<table1.cells[0].colSpan; i++)
    {
        width  += table2.cells[i].offsetWidth+2;
    }
    //table1.style.width = table2.;
    width += 18;
    table1.cells[0].style.width = width + 'px';
    for (var i=table1.cells[0].colSpan; i< table2.cells.length; i++)
    {
        j = (i-table1.cells[0].colSpan)+1;
        aux = table2.cells[i].offsetWidth > table1.cells[j].offsetWidth ? table2.cells[i].offsetWidth : table1.cells[j].offsetWidth;
        width += aux;
        table1.cells[j].style.width = aux + 'px';
        table2.cells[i].style.width = (aux + (table2.cells[i].offsetWidth < table1.cells[j].offsetWidth ? 3 : 0) ) + 'px';
        //table1.rows[0].cells[j].style.width = '400px';
        //alert(table2.rows[1].cells[i].style.width);
        //alert(table2.rows[1].cells[i].offsetWidth);
    }
    table1 = document.getElementById( 't' + id1 );
    table2 = document.getElementById( 't' + id2 );
    table1.style.width = width + 'px';
    table2.style.width = (width-16) + 'px';
    //for ( var i in table2 ) alert(i);
}