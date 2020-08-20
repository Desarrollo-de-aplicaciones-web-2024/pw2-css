<?php
require_once 'PruebasHTML.php';

class E02_Test extends PruebasHTML {
    const DIR = 'E02' . DIRECTORY_SEPARATOR;
    const ARCHIVO = self::DIR . 'index.html';

    public function testSolicionCorrecta(){
        $archivo        = $archivo = $this->root . self::ARCHIVO;
        $hoja_estilos   = $this->root . self::DIR  . 'estilos.css';
        $csstidy        = $this->root . 'csstidy/class.csstidy.php';

        $this->assertFileExists($hoja_estilos, "No existe el archivo $hoja_estilos");

        $file    = file($hoja_estilos);
        $count   = count($file);

        $this->assertGreaterThan(0, $count, 'El archivo ' . $hoja_estilos . ' está vacío');


        $this->assertFileExists($csstidy, "No existe el archivo $csstidy");

        require_once $csstidy;

        $codigo = file_get_contents($hoja_estilos);

        $tidy = new csstidy();

        $tidy->set_cfg('compress_colors', false);
        $tidy->set_cfg('compress_font-weight', false);
        $tidy->set_cfg('merge_selectors', 1);


        $tidy->parse($codigo);

        $this->assertNotEmpty($tidy->css, 'No se pudo procesar el archivo estilos.css');
        $this->assertIsArray($tidy->css, 'No se pudo procesar el archivo estilos.css');

        $color      = false;
        $background = false;

        foreach($tidy->css as $selectores){
            foreach($selectores as $selector => $propiedades){

                foreach($propiedades as $propiedad => $valor){
                    switch(strtolower($propiedad)) {
                        case 'color':
                            $color = true;
                            break;
                        case 'background':
                        case 'background-color':
                            $background = true;
                            break;
                    }
                }
            }
        }

        $this->assertTrue($color, 'Ajusta el color de fuente de algún elemento');
        $this->assertTrue($background, 'Ajusta el color de fondo de algún elemento');



        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $this->estructuraCorrectaDocumentoHTML($archivo);

        $str = str_ireplace(self::DOC_TYPE, '', file_get_contents($archivo));

        $doc = new DOMDocument();

        libxml_use_internal_errors(true);
        $doc->loadHTML($str);

        $this->assertIsObject($doc, 'No se pudo leer la estructura del documento, revisa que sea un documento HTML válido');

        $links = $doc->getElementsByTagName('link');

        $this->assertGreaterThan(1, count($links), 'Falta la inclusión de una fuente de Google Fonts');

        $t_href = array();
        foreach($links as $link){
            $href  = trim($link->getAttribute('href'));
            if($href != 'estilos.css') {
                $t_href[] = $href;
            }
        }

        foreach($t_href as $href) {
            $this->assertStringContainsStringIgnoringCase('https://fonts.googleapis.com', $href, 'Falta la inclusión de una fuente de Google Fonts');
        }



        $images = $doc->getElementsByTagName('img');

        $this->assertEquals(3, count($images), 'Cantidad incorrecta de elementos <img>');

        foreach($images as $image){
            $src  = trim($image->getAttribute('src'));
            $this->assertNotEquals('warning.png', $src, 'No has cambiado todos los iconos de la sección footer');
        }
    }

}