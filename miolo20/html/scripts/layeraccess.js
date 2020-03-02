function writeToLayer(id, text)
    {
    x = xGetElementById(id);
    x.innerHTML = text;
    }

function readFromLayer(id)
    {
    var text;
    x = xGetElementById(id);
    text = x.innerHTML;
    return text;
    }

function showLayer(id, action)
    {
    x = xGetElementById(id);

    if (action)
        {
        xShow(x);
        }

    else
        {
        xHide(x);
        }
    }

function changeContent(id, str)
    {
    writeToLayer(id, str);
    }

function getPrintContent()
    {
    return readFromLayer('contentLayer');
    }

function toggleLayer(id)
    {
    x = xGetElementById(id);
    v = x.style.visibility;
    alert(v);

    if (v == 'visible')
        {
        xHide(x);
        }

    else
        {
        xShow(x);
        }
    }
