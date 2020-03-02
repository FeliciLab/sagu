echo "Copiando Tema original"
cp gnuteca gnutecaRed -HR
cd gnutecaRed
echo "Gerando tema vermelho"
rpl gnuteca gnutecaRed * -R
rpl '#246' '#600' *
rpl 'blue' 'red' *
rpl '#48f' 'red' *
rpl '#5bf' '#f55' *
#rpl '#333' '#600' *
#rpl 'black' '#400' *
#rpl '#555' '#333' *
#rpl '#ccc' '#999' *
#rpl '#eee' '#bbb' *
#rpl '#f9f9f9' '#ddd' *
#rpl 'white' '#efefef' *
rpl 'orange' 'red' *
rpl '#fff3a3' '#bbb' *
echo "Lembre de criar o link na pasta themes do miolo seja for necessário."
