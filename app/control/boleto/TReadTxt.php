<?php class TReadTxt {
    private $_file;
    
    function __construct($file) 
    {
        $this->_file = $file;
    }
    
    public function set_file($file) 
    {
        $this->_file = $file;
    }
    
    public function abre() 
    {
        $fp = fopen ($this->_file,"r");
        //$conteudo[] = array();
		while ($data = utf8_encode( fgets ( $fp, 4096) ) ) 
		{
            $conteudo[] = $data;
        }
        return $conteudo;
    }

    public function pasta( $caminho )
    {
        
        $okp = 0;
        if( !is_dir($caminho))
        {
            $okp = 1; // A Pasta Existe
        } else
        {
            $okp = 0; // A Pasta não Existe
        }
        return $okp;
    }

} ?>