<?php

/**
 * PHP obfuscator
 *
 * PHP version 7.4.4
 *
 * This script will remove all the page setup of an existing PHP source
 * code i.e. remove comments, unneeded carriage returns, multiples spaces, ...
 *
 * Then, once minified, the script will obfuscate your code based on your settings.
 *
 * @package Php_Obfuscator
 * @author AVONTURE Christophe <christophe@avonture.be>
 * @license MIT
 */

namespace Avonture;

 // phpcs:disable PSR1.Files.SideEffects

 /**
  * The minifier class
  */
class Minify
{
    /**
     * Input file to minify and obfuscate
     *
     * @var string
     */
    private $input="";

    /**
     * Output filename; that file will be created
     *
     * @var string
     */
    private $output="";

    /**
     * Constructor
     *
     * @param string $source Input file to minify and obfuscate
     * @param string $outFile Output filename; that file will be created
     *
     */
    public function __construct(string $source, string $outFile)
    {
        $this->input = $source;
        $this->output = $outFile;
    }

    /**
     * Run a preg_replace and replace all occurences
     *
     * @param string $pattern The regex to search for
     * @param string $replacement The replacement value
     * @param string $content The string where to search/replace
     *
     * @return string
     */
    private function pregReplaceAll(string $pattern, string $replacement, string $content): string
    {
        while (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }

    /**
     * Make sure the replacedBy value is used only once. Die otherwise.
     *
     * @param array<string, string> $arr The list of keys to search and the associate value
     *
     * @return void
     */
    private function ensureReplacedByIsUsedOnlyOnce(array $arr): void
    {
        $arrUnique = [];

        foreach ($arr as $search => $replaceBy) {
            if (isset($arrUnique[$replaceBy])) {
                die(
                    printf(
                        "\nError: The value %s is used more than once in your settings.json; ".
                        "please solve this error.\n",
                        $replaceBy
                    )
                );
            }

            $arrUnique[$replaceBy]='';
        }
    }

    /**
     * Read the settings.json file, process the list of replacements.
     *
     * Aim : make more difficult to read the php source code
     *
     *    "replace":{
     *        "obfuscate":"octE",
     *        "$value":"$v",
     *        "$content":"$c",
     *        "removeComments":"rmCts",
     *        ...
     *     }
     *
     * @param string $content The content to obfuscate
     *
     * @return string
     */
    private function obfuscate(string $content): string
    {
        $file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'settings.json';

        if (!(file_exists($file))) {
            return $content;
        }

        $json=json_decode(file_get_contents($file, FILE_USE_INCLUDE_PATH), true);

        $this->ensureReplacedByIsUsedOnlyOnce($json['replace']);

        foreach ($json['replace'] as $search => $replaceBy) {
            if (strpos($content, $search)) {
                {}
                $content=str_replace($search, $replaceBy, $content);
            }
        }

        return $content;
    }

    /**
     * Remove PHP comments from the source file
     *
     * @param string $content Content of the .php file to process
     *
     * @return string
     */
    private function removeComments(string $content): string
    {
        $commentTokens = array(T_COMMENT);

        // PHP 5+
        if (defined('T_DOC_COMMENT')) {
            $commentTokens[] = T_DOC_COMMENT;
        }

        // PHP 4
        if (defined('T_ML_COMMENT')) {
            $commentTokens[] = T_ML_COMMENT;
        }

        $tokens = token_get_all($content);

        $newContent = '';

        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (in_array($token[0], $commentTokens)) {
                    continue;
                }

                // 357=T_WHITESPACE
                if ($token[0]==T_WHITESPACE) {
                    if (strpos($token[1], "\n") !== false) {
                        $token = trim($token[1])."\n";
                    } else {
                        $token = ' '.trim($token[1]);
                    }
                } elseif ($token[0] == T_ENCAPSED_AND_WHITESPACE) {
                    // Don't touch the characters inside a string
                    // 312=T_ENCAPSED_AND_WHITESPACE
                    $token = $token[1];
                } else {
                    $token = ltrim($token[1]);
                }
            }

            $newContent .= $token;
        }

        return $newContent;
    }

    /**
     * Remove unneeded spaces
     *
     * @param string $content Content of the .php file to process
     *
     * @return string
     */
    private function trimContent(string $content): string
    {
        $trimmedContent ='';

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $content) as $line) {
            $line=rtrim(ltrim($line, ' '), ' ');

            if (($line=='')||($line==null)||($line=="\n")||($line=="\r")) {
                continue;
            }

            $trimmedContent.=$line.PHP_EOL;
        }

        // Remove the ending carriage return and extra ending space
        return rtrim(rtrim($trimmedContent, PHP_EOL), ' ');
    }

    /**
     * Remove unneeded linefeed
     *
     * @param string $content Content of the .php file to process
     *
     * @return string
     */
    private function removeUnneededLF(string $content): string
    {
        // Remove multiples CRLF and replace by just one
        $content = $this->pregReplaceAll('/[\r\n]{2,}/', "\n", $content);

        // Line ending by ";" or "}" followed by a CR/LF/CRLF and a keywork (break, foreach, ...)
        $content = $this->pregReplaceAll(
            '/([{;}])[\r\n]+'.
            '(break|continue|define|die|echo|foreach|header|printf|private|protected|public|'.
            'require_once|return|self\:|sprintf|static|throw|while)'.
            '(.*)/',
            "$1$2$3$4",
            $content
        );

        // Line ending by "}" followed by a CR/LF/CRLF and a keywork (public, return, ...)
        $content = $this->pregReplaceAll(
            '/;[\r\n]+(if\(|public|return)(.*)/',
            ";$1$2",
            $content
        );

        // Replace ; followed CR/LF/CRLF and } and remove the CR/LF/CRLF
        $content = $this->pregReplaceAll('/;[\r\n]+}/', ';}', $content);

        // Line ending by one character in ";,{}" followed by CR/LF/CRLF and a variable (i.e. starting by a dollar)
        $content = $this->pregReplaceAll('/([;,{}])[\r\n]\$(.*)/', "$1\$$2", $content);

        // Replace } followed by CR/LF/CRLF and a second }
        $content = $this->pregReplaceAll('/}[\r\n]+}/', "}}", $content);

        // Replace ) followed by CR/LF/CRLF and a second )
        $content = $this->pregReplaceAll('/\)[\r\n]+\)/', "))", $content);

        // Replace : followed by a keyword (bool, boolean, ... string)
        $content = $this->pregReplaceAll('/: (bool|string)[\r\n]+{(.*)/', ": $1{ $2", $content);

        // Replace } followed by CR/LF/CRLF and a second }
        $content = $this->pregReplaceAll('/}[\r\n]+}/', "}}", $content);

        // Replace printf( followed by CR/LF/CRLF and " or '
        $content = $this->pregReplaceAll('/(s?printf?\()[\r\n]+(\"|\')/', "$1$2", $content);

        // Misc
        $content=str_replace("break;\ncase", "break;case", $content);

        // ', followed by a new line and by a new ' can be on the same line
        $content=str_replace("',\n'", "','", $content);
        $content=str_replace("'.\n'", "'.'", $content);
        $content=str_replace(" :\necho", ":echo", $content);

        // <?php{ is forbidden
        $content=str_replace("<?php}", "<?php\n}", $content);

        return $content;
    }

    /**
     * Remove unneeded spaces
     *
     * @param string $content Content of the .php file to process
     *
     * @return string
     */
    private function removeUnneededSpaces(string $content): string
    {
        $content=str_replace(' || ', '||', $content);
        $content=str_replace(' && ', '&&', $content);
        $content=str_replace(' ? ', '?', $content);
        $content=str_replace(' : ', ':', $content);
        $content=str_replace(' . ', '.', $content);
        $content=str_replace(' =', '=', $content);
        $content=str_replace('= ', '=', $content);
        $content=str_replace(' {', '{', $content);
        $content=str_replace(' }', '}', $content);
        $content=str_replace('} ', '}', $content);
        $content=str_replace('if (', 'if(', $content);
        $content=str_replace(", $", ",$", $content);
        $content=str_replace("( (", "((", $content);
        $content=str_replace(") )", "))", $content);

        return $content;
    }

    /**
     * Minify and obfuscate
     *
     * @return void
     */
    public function doIt(): void
    {
        // Get the file extension
        $parts = \explode('.', \basename($this->input));
        $fileExtension = \strtolower(array_pop($parts));

        if ('php' !== $fileExtension) {
            return;
        }

        // Get the content of the source file
        $content = file_get_contents($this->input);

        $newContent = $this->removeComments($content);
        $newContent = $this->trimContent($newContent);
        $newContent = $this->removeUnneededSpaces($newContent);
        $newContent = $this->removeUnneededLF($newContent);

        // Finally, obfuscate the content i.e. replace some string by other ones
        $newContent = $this->obfuscate($newContent);

        // And save the compressed PHP file
        echo "\033[33mCreate $this->output\033[0m\n\n";
        file_put_contents($this->output, $newContent);
    }
}

// region Entry point

echo "\033[32mPHP minifier and obfuscator tool\033[0m\n";
echo "\033[32m================================\033[0m\n\n";

parse_str(implode('&', array_slice($argv, 1)), $_GET);

if (!isset($_GET['input']) || (!isset($_GET['output']))) {
    echo "Use:\n\n";
    echo "   * input: the PHP source to process i.e. to minify and obfuscate\n";
    echo "   * output: name of output file that will contains the minified version\n\n";
    echo "Sample: php " . basename(__FILE__) . " input=Source.php output=Souce_min.php\n";
    die();
}

$input = $_GET['input'];
$output = $_GET['output'];

$path=\realpath($input);

if (!is_file($path)) {
    throw new \Exception(
        printf(
            "The file %s doesn't exists\n",
            $input
        )
    );
}

$minify=new Minify($input, $output);
$minify->doIt();
unset($minify);
// endregion
