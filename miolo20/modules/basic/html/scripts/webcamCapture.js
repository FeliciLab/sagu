// WebcamCapture.js v1.0 para Gnuteca
// Captura fotos do usuário a ser cadastrado via webcam com o recurso "getUserMedia"
// Baseado no projeto JPEGCam: http://code.google.com/p/jpegcam/
// Solis, Cooperativa de SoluÃ§Ãµes Livres Ltda.
// Escrito por Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
// Mantido por Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
// Licença GNU/GPL

// Criação da classe principal com todos os métodos responsáveis por lidar com a webcam.
var WebcamCapture = {
    userMedia: true,
    container: null,
    video: null,
    context: null,
    canvas: null,
    stream: null,
    executed: false, // true quando a webcam já foi executada.
    live: false, // true quando a webcam está transmitindo o vídeo.
    loaded: false, // true quando a webcam está carregada.
    videoWidth: 640,
    videoHeight: 480,
    croppedImgWidth: 360,
    croppedImgHeight: 480,
    imgFormat: 'jpeg',
    imgJpegQuality: 100,
    imgLeftMargin: -140,
    imgTopMargin: -15,
    imgDefTopMargin: -15,
    
    // Verifica os recursos que serão utilizados.
    init: function()
    {
        // Procura o suporte do browser ao "getUserMedia"
        navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
        window.URL = window.URL || window.webkitURL || window.mozURL || window.msURL;

        this.userMedia = this.userMedia && !!navigator.getUserMedia && !!window.URL;

    },

    // Cria os elementos necessários para captura e exibirão da imagem.
    // Argumento: elemento que irá receber a stream de video.
    attach: function(elem)
    {
        if(this.userMedia)
        {
            elem = document.getElementById(elem);
            
            this.container = elem;
            
            if(typeof this.container !== 'undefined')
            {
                // Configurações do elemento que conterá a stream de video.
                var video = document.createElement('video');
                video.setAttribute('autoplay', 'autoplay');
                video.style.position = 'absolute';
                video.style.top = '0px';
                video.style.width = this.videoWidth + 'px';
                video.style.height = this.videoHeight + 'px';
                video.style.marginLeft = this.imgLeftMargin + 'px';

                if(!WebcamCapture.executed)
                {
                    video.style.marginTop = this.imgTopMargin + 'px';    
                    WebcamCapture.executed = true;
                }

                // Adiciona o video ao container.
                //this.container.innerHTML = ''; // Esvazia qualquer coisa que esteja no container.
                this.container.style.position = 'relative';
                this.container.appendChild(video);
                this.video = video;

                // Cria um canvas onde será desenhada a imagem capturada.
                var canvas = document.createElement('canvas');
                canvas.width = this.croppedImgWidth;
                canvas.height = this.croppedImgHeight;

                var context = canvas.getContext('2d');
                this.context = context;
                this.canvas = canvas;

                // Acessa o recurso "getUserMedia".
                navigator.getUserMedia(
                {
                    "audio": false,
                    "video": true

                },

                // Callback de sucesso. Se conseguir acesso, exibe imagem. 
                function(stream)
                {
                    // Definições do browser.
                    if (video.mozSrcObject !== undefined)
                    {
                        video.mozSrcObject = stream;

                    }
                    else
                    {
                        video.src = (window.URL && window.URL.createObjectURL(stream)) || stream;

                    }
                    
                    video.play();

                    WebcamCapture.stream = stream;
                    WebcamCapture.loaded = true;
                    WebcamCapture.live = true;

                },

                // Callback de erro.
                function(erro)
                {
                    alert("A webcam nÃ£o pode ser acessada.");

                });
                
            }
            
        }
	else
        {
            alert("O browser nÃ£o suporta o recurso necessÃ¡rio para captura de imagens via Webcam. Verifique se o seu navegador de internet estÃ¡ atualizado!");
            
        }

    },

    // Desliga a cÃ¢mera.
    shutdown: function()
    {
        if (this.userMedia)
        {
            try
            {
                this.stream.stop();
                
            }
            catch(e)
            {;}

            delete this.stream;
            delete this.canvas;
            delete this.context;
            delete this.video;

        }
	
        if(this.executed !== false)
        {
            if(typeof this.container !== 'undefined')
            {
                delete this.container;
                
            }
            
        }
        	
        this.loaded = false;
        this.live = false;

    },

    // MÃ©todo que tira foto e retorna a mesma no formato de base64.
    snap: function()
    {
        if((!this.loaded) || (!this.live))
        {
            alert('A webcam nÃ£o foi carregada ainda!');

        }
        else
        {
            if (this.userMedia)
            {
                var sourceX = 140,
                    sourceY = 0,
                    sourceWidth = this.croppedImgWidth,
                    sourceHeight = this.croppedImgHeight,
                    destX = 0,
                    destY = 0,
                    destWidth = this.croppedImgWidth,
                    destHeight = this.croppedImgHeight;
	                   
                this.context.drawImage(this.video, sourceX, sourceY, sourceWidth, sourceHeight, destX, destY, destWidth, destHeight);

                return this.canvas.toDataURL('image/' + this.imgFormat, this.imgJpegQuality / 100);

            }

        }

    },
    
    reset: function()
    {
        if(this.live)
        {
            this.shutdown();
                    
        }
        
        this.executed = false;
        
    }

};

WebcamCapture.init();