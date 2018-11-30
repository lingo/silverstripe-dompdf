<?php

/**
 * SilverStripe wrapper for DOMPDF
 */

// reference the Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;

class SS_DOMPDF
{
    protected $dompdf;

    public function __construct()
    {
        // inhibit DOMPDF's auto-loader
        define('DOMPDF_ENABLE_AUTOLOAD', false);

        $options      = self::get_default_pdf_options();
        $this->dompdf = new Dompdf($options);
        $this->dompdf->set_base_path(BASE_PATH);
        $this->dompdf->set_host(Director::absoluteBaseURL());

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed'=> true
            ]
        ]);
        $this->dompdf->setHttpContext($context);
    }

    public function setOption($key, $value)
    {
        $this->dompdf->set_option($key, $value);
    }

    public function set_paper($size, $orientation)
    {
        $this->dompdf->set_paper($size, $orientation);
    }

    public function setHTML($html)
    {
        $this->dompdf->load_html($html);
    }

    public function setHTMLFromFile($filename)
    {
        $this->dompdf->load_html_file($filename);
    }

    public function render()
    {
        $this->dompdf->render();
    }

    public function output($options = null)
    {
        return $this->dompdf->output($options);
    }

    public function stream($outfile, $options = '')
    {
        return $this->dompdf->stream($this->addFileExt($outfile), $options);
    }

    public function toFile($filename = "file", $folder = "PDF")
    {
        $filename = $this->addFileExt($filename);
        $filedir  = ASSETS_DIR . "/$folder/$filename";
        $filepath = ASSETS_PATH . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $filename;
        $folder   = Folder::find_or_make($folder);
        $output   = $this->output();
        if ($fh       = fopen($filepath, 'w')) {
            fwrite($fh, $output);
            fclose($fh);
        }
        $file  = new File();
        $file->setName($filename);
        $file->Filename = $filedir;
        $file->ParentID = $folder->ID;
        $file->write();
        return $file;
    }

    public function addFileExt($filename, $new_extension = 'pdf')
    {
        if (strpos($filename, "." . $new_extension)) {
            return $filename;
        }
        $info = pathinfo($filename);
        return $info['filename'] . '.' . $new_extension;
    }

    /**
     * uesful function that streams the pdf to the browser,
     * with correct headers, and ends php execution.
     */
    public function streamdebug()
    {
        header('Content-type: application/pdf');
        $this->stream('debug', array('Attachment' => 0));
        die();
    }


	/**
	* Set default options
	* These are converted from old dompdf_config.inc.php, excepting the following
	* (obsolete?) keys:
	*
	*    define("DOMPDF_UNICODE_ENABLED", true);
	*    define("DOMPDF_ENABLE_CSS_FLOAT", true);
	*    define("DOMPDF_AUTOLOAD_PREPEND", false);
	*
	* @return Dompdf\Options
	*/
	public static function get_default_pdf_options()
	{
		$dompdfDir = str_replace(DIRECTORY_SEPARoATOR, '/', BASE_PATH . "/vendor/dompdf/dompdf");

		$options   = new \Dompdf\Options();
		$options->setAdminUsername('');
		$options->setAdminPassword('');
		$options->setRootDir($dompdfDir);
		$options->setChroot(realpath($dompdfDir));
		$options->setFontDir($dompdfDir    . '/lib/fonts');
		$options->setTempDir(TEMP_FOLDER   . '/dompdf/tmp');
		$options->setFontCache(TEMP_FOLDER . '/dompdf/fontcache');
		$options->setIsFontSubsettingEnabled(true);
		$options->setPdfBackend('CPDF');
		$options->setDefaultMediaType('screen');
		$options->setDefaultPaperSize('A4');
		$options->setDefaultFont('serif');
		$options->setDpi(96);
		$options->setIsPhpEnabled(false);
		$options->setIsJavascriptEnabled(true);
		$options->setIsRemoteEnabled(true);
		$options->setLogOutputFile(TEMP_FOLDER . '/dompdf/log.htm');
		$options->setFontHeightRatio(1.1);
		$options->setIsHtml5ParserEnabled(false);
		$options->setDebugPng(false);
		$options->setDebugKeepTemp(false);
		$options->setDebugCss(false);
		$options->setDebugLayout(false);
		$options->setDebugLayoutLines(true);
		$options->setDebugLayoutBlocks(true);
		$options->setDebugLayoutInline(true);
		$options->setDebugLayoutPaddingBox(true);
		return $options;
	}
}
