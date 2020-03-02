// Obtido de http://wbruno.com.br/2012/08/02/mascara-campo-de-telefone-em-javascript-com-regex-nono-digito-telefones-sao-paulo/

/* Máscaras ER */
function mascara(o,f){
    v_obj=o
    v_fun=f
    setTimeout("execmascara()",1)
}
function execmascara(){
    v_obj.value=v_fun(v_obj.value)
}
function mtel(v){
    if ( v.length > 15 )
    {
        return v.substring(0, (v.length - 1));
    }
    
    v=v.replace(/\D/g,"");             //Remove tudo o que não é dígito
    v=v.replace(/^(\d{2})(\d)/g,"($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
    v=v.replace(/(\d)(\d{4})$/,"$1-$2");    //Coloca hífen entre o quarto e o quinto dígitos
    
    return v;
}