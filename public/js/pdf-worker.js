// Web Worker para processar PDFs em background
// Isso evita travar a interface do usuário

// Importar PDF.js no worker
importScripts('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js');

// Configurar PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

let pdfDoc = null;

// Receber mensagens da thread principal
self.addEventListener('message', async (e) => {
    const { type, data } = e.data;

    try {
        switch (type) {
            case 'load':
                await loadPDF(data.url);
                break;
            case 'render':
                await renderPage(data.pageNum, data.scale, data.quality);
                break;
            case 'getInfo':
                getInfo();
                break;
        }
    } catch (error) {
        self.postMessage({
            type: 'error',
            error: error.message
        });
    }
});

async function loadPDF(url) {
    try {
        const loadingTask = pdfjsLib.getDocument({
            url: url,
            disableAutoFetch: true,
            disableStream: false,
            disableRange: false,
            rangeChunkSize: 65536,
            useSystemFonts: true,
        });

        loadingTask.onProgress = (progress) => {
            if (progress.total > 0) {
                const percent = Math.round((progress.loaded / progress.total) * 100);
                self.postMessage({
                    type: 'progress',
                    percent: percent
                });
            }
        };

        pdfDoc = await loadingTask.promise;

        self.postMessage({
            type: 'loaded',
            numPages: pdfDoc.numPages
        });
    } catch (error) {
        self.postMessage({
            type: 'error',
            error: error.message
        });
    }
}

async function renderPage(pageNum, scale, quality) {
    if (!pdfDoc) {
        throw new Error('PDF não carregado');
    }

    const page = await pdfDoc.getPage(pageNum);
    
    // Ajustar escala baseado na qualidade
    let renderScale = scale;
    if (quality === 'preview') {
        renderScale = scale * 0.5;
    } else if (quality === 'low') {
        renderScale = scale * 0.6;
    } else if (quality === 'medium') {
        renderScale = scale * 0.75;
    }

    const viewport = page.getViewport({ scale: renderScale });

    // Criar canvas offscreen
    const canvas = new OffscreenCanvas(viewport.width, viewport.height);
    const context = canvas.getContext('2d');

    const renderContext = {
        canvasContext: context,
        viewport: viewport,
        intent: 'display',
        enableWebGL: false,
        renderInteractiveForms: false,
    };

    await page.render(renderContext).promise;

    // Converter canvas para ImageBitmap (mais eficiente)
    const imageBitmap = await createImageBitmap(canvas);

    self.postMessage({
        type: 'rendered',
        pageNum: pageNum,
        imageBitmap: imageBitmap,
        width: viewport.width,
        height: viewport.height,
        quality: quality
    }, [imageBitmap]); // Transferir ownership do bitmap
}

function getInfo() {
    if (!pdfDoc) {
        self.postMessage({
            type: 'info',
            info: null
        });
        return;
    }

    self.postMessage({
        type: 'info',
        info: {
            numPages: pdfDoc.numPages
        }
    });
}
