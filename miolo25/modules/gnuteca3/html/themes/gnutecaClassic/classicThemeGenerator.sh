echo "Copiando Tema original"
svn export gnuteca gnutecaClassic
cd gnutecaClassic
echo "Gerando tema classico"
rpl gnuteca gnutecaClassic * -R
rpl '#246' '#444' *
rpl '#48f' '#287eb0' *
rpl '#5bf' '#2B92CE' *
#rpl '#333' '#600' *
#rpl 'black' '#400' *
#rpl '#555' '#333' *
#rpl '#ccc' '#999' *
#rpl '#eee' '#bbb' *
#rpl '#f9f9f9' '#ddd' *
#rpl 'white' '#efefef' *
rpl '#ddd' '#333' *
rpl 'orange' '#87CEFA' *
rpl '#f9f9f9' '#eee' *
rpl '#fff3a3' '#78c2ec' *
rpl 'solid 2px' 'solid 1px' *
rpl 'gnutecaClassic.closeAction' 'gnuteca.closeAction' *
rpl 'text-shadow:1px 1px 1px red;' '' miolo.css 
rpl 'padding-top:20px !important;' 'padding-top:10px !important;' miolo.css
rpl 'padding-bottom: 20px !important;' 'padding-bottom: 10px !important;;' miolo.css
rpl 'border-collapse:collapse;' '' miolo.css

echo "Lembre de criar o link na pasta themes do miolo seja for necessário."
