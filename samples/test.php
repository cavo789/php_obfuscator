<?php
namespace Avonture;

class mF
{
    private $it="";
    private $ot="";
    public function __construct(string $s, string $o)
    {
        $this->it=$s;
        $this->ot=$o;
    }
    private function prA(string $p, string $r, string $c): string
    {
        while (preg_match($p, $c)) {
            $c=preg_replace($p, $r, $c);
        }
        return $c;
    }
    private function eOn(array $arr): void
    {
        $aU=[];
        foreach ($arr as $ch=> $rb) {
            if (isset($aU[$rb])) {
                die(
printf(
    "\nError: The value %s is used more than once in your settings.json; ".
"please solve this error.\n",
    $rb
));
            }
            $aU[$rb]='';
        }
    }
    private function octE(string $c): string
    {
        $file=dirname(__FILE__).DIRECTORY_SEPARATOR.'settings.json';
        if (!(file_exists($file))) {
            return $c;
        }
        $j=json_decode(file_get_contents($file, FILE_USE_INCLUDE_PATH), true);
        $this->eOn($j['replace']);
        foreach ($j['replace'] as $ch=> $rb) {
            if (strpos($c, $ch)) {
                {}
                $c=str_replace($ch, $rb, $c);
            }
        }
        return $c;
    }
    private function rmCts(string $c): string
    {
        $n=array(T_COMMENT);
        if (defined('T_DOC_COMMENT')) {
            $n[]=T_DOC_COMMENT;
        }
        $ks=token_get_all($c);
        $sC='';
        foreach ($ks as $k) {
            if (is_array($k)) {
                if (in_array($k[0], $n)) {
                    continue;
                }
                if ($k[0]==T_WHITESPACE) {
                    if (strpos($k[1], "\n") !==false) {
                        $k=trim($k[1])."\n";
                    } else {
                        $k=' '.trim($k[1]);
                    }
                } elseif ($k[0]==T_ENCAPSED_AND_WHITESPACE) {
                    $k=$k[1];
                } else {
                    $k=ltrim($k[1]);
                }
            }
            $sC .=$k;
        }
        return $sC;
    }
    private function tCn(string $c): string
    {
        $tnT='';
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $c) as $l) {
            $l=rtrim(ltrim($l, ' '), ' ');
            if (($l=='')||($l==null)||($l=="\n")||($l=="\r")) {
                continue;
            }
            $tnT.=$l.PHP_EOL;
        }
        return rtrim(rtrim($tnT, PHP_EOL), ' ');
    }
    private function rUnL(string $c): string
    {
        $c=$this->prA('/[\r\n]{2,}/', "\n", $c);
        $c=$this->prA(
            '/([{;}])[\r\n]+'.'(break|continue|define|die|echo|foreach|header|printf|private|protected|public|'.'require_once|return|self\:|sprintf|static|throw|while)'.'(.*)/',
            "$1$2$3$4",
            $c
        );
        $c=$this->prA(
            '/;[\r\n]+(if\(|public|return)(.*)/',
            ";$1$2",
            $c
        );
        $c=$this->prA('/;[\r\n]+}/', ';}', $c);
        $c=$this->prA('/([;,{}])[\r\n]\$(.*)/', "$1\$$2", $c);
        $c=$this->prA('/}[\r\n]+}/', "}}", $c);
        $c=$this->prA('/\)[\r\n]+\)/', "))", $c);
        $c=$this->prA('/: (bool|boolean|int|integer|string|void)[\r\n]+{(.*)/', ": $1{ $2", $c);
        $c=$this->prA('/}[\r\n]+}/', "}}", $c);
        $c=$this->prA('/(s?printf?\()[\r\n]+(\"|\')/', "$1$2", $c);
        $c=str_replace("break;\ncase", "break;case", $c);
        $c=str_replace("',\n'", "','", $c);
        $c=str_replace("'.\n'", "'.'", $c);
        $c=str_replace(" :\necho", ":echo", $c);
        $c=str_replace("<?php
}", "<?php\n}", $c);
        return $c;
    }
    private function rUsP(string $c): string
    {
        $c=str_replace('||', '||', $c);
        $c=str_replace('&&', '&&', $c);
        $c=str_replace('?', '?', $c);
        $c=str_replace(':', ':', $c);
        $c=str_replace('.', '.', $c);
        $c=str_replace('=', '=', $c);
        $c=str_replace('=', '=', $c);
        $c=str_replace('{', '{', $c);
        $c=str_replace('}', '}', $c);
        $c=str_replace('}', '}', $c);
        $c=str_replace('if(', 'if(', $c);
        $c=str_replace(",$", ",$", $c);
        $c=str_replace("((", "((", $c);
        $c=str_replace("))", "))", $c);
        return $c;
    }
    private function lint(): void
    {
        $ot=[];
        $w="";
        exec("php -l $this->ot", $ot, $w);
        if (0===$w) {
            echo "\033[32mSUCCESS!\033[0m\n";
            return;
        }
        echo "\n\033[31mFAILURE!\033[0m\n\n";
        echo "The file $this->ot contains error:\n\n";
        print_r($ot);
    }
    public function I(): void
    {
        $a=\explode('.', \basename($this->it));
        $x=\strtolower(array_pop($a));
        if ('php' !==$x) {
            return;
        }
        $c=file_get_contents($this->it);
        $sC=$this->rmCts($c);
        $sC=$this->tCn($sC);
        $sC=$this->rUsP($sC);
        $sC=$this->rUnL($sC);
        $sC=$this->octE($sC);
        echo "\033[33mCreate $this->ot\033[0m\n\n";
        file_put_contents($this->ot, $sC);
        $this->lint();
    }
}echo "\033[32mPHP minifier and obfuscator tool\033[0m\n";echo "\033[32m================================\033[0m\n\n";
parse_str(implode('&', array_slice($argv, 1)), $_GET); if (!isset($_GET['input'])||(!isset($_GET['output']))) {
    echo "Use:\n\n";
    echo "   * input: the PHP source to process i.e. to mfy and octE\n";
    echo "   * output: name of output file that will contains the minified version\n\n";
    echo "Sample: php ".basename(__FILE__)." input=Source.php output=Souce_min.php\n";
    die();
}$it=$_GET['input'];$ot=$_GET['output'];$h=\realpath($it); if (!is_file($h)) {
    throw new \Exception(
        printf(
    "The file %s doesn't exists\n",
    $it
)
    );
}$xZ0=new mF($it, $ot);$xZ0->I();
unset($xZ0);
