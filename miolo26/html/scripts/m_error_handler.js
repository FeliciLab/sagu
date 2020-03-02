
document.addDebugInformation = function(msg)
{
    doc = top.frames['content'] ? top.frames['content'].document : top.document;
    div = doc.getElementById('debugDiv');

    if(div)
    {
        div.innerHTML    += msg;
    }
    else
    {
        div               = doc.createElement('div');
        div.id            = 'debugDiv';
        div.className     = 'm-prompt-box-information';
        div.style.width   = '100%';
        div.style.height  = '100px';
        div.style.overflow='auto';
        //div.style.position='relative';
        div.innerHTML     = msg;
        doc.body.insertBefore(div,doc.body.lastChild);
    }
};
