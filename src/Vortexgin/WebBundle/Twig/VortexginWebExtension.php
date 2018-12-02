<?php

namespace Vortexgin\WebBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Vortexgin\LibraryBundle\Manager\FormTokenizerManager;

/**
 * VortexginWebExtension
 * 
 * @category TwigExtension
 * @package  AppTwig
 * @author   Tommy <vortexgin@gmail.com>
 * @license  Apache 2.0 (https://opensource.org/licenses/Apache-2.0)
 * @link     https://github.com/vortexgin/corebundle
 */
class VortexginWebExtension extends AbstractExtension
{

    /**
     * Form tokenizer
     * 
     * @var \Vortexgin\LibraryBundle\Manager\FormTokenizerManager 
     */
    private $_formTokenizer;

    /**
     * Contruct
     * 
     * @param \Vortexgin\LibraryBundle\Manager\FormTokenizerManager $formTokenizer Form tokenizer
     * 
     * @return void
     */
    public function __construct(FormTokenizerManager $formTokenizer)
    {
        $this->_formTokenizer = $formTokenizer;
    }

    /**
     * Declare twig filter extension
     * 
     * @return array
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('form_generate_token', array($this, 'formTokenizerGenerateToken')),
            new TwigFilter('filter_admin_actions', array($this, 'filterActions')),
            new TwigFilter('parse_url', array($this, 'parseUrl')),
        );
    }

    /**
     * Twig Filter Generate Form Token
     * 
     * @param string $prefix Prefix
     * 
     * @return string
     */
    public function formTokenizerGenerateToken($prefix)
    {
        return $this->_formTokenizer->generateToken($prefix);
    }

    /**
     * Twig Filter Remove export button
     * 
     * @param array $itemActions Item actions
     * @param array $item        Item
     * 
     * @return string
     */
    public function filterActions(array $itemActions, $item)
    {
        unset($itemActions['export']);
        unset($itemActions['import']);
        return $itemActions;
    }

    /**
     * Twig Filter Parse URL
     * 
     * @param string $url   URL to parse
     * @param int    $parse Component to fetch
     * 
     * @return string
     */
    public function parseUrl($url, $parse)
    {
        return parse_url($url, $parse);
    }

    /**
     * Get Name Extension
     * 
     * @return string
     */
    public function getName()
    {
        return 'app_extension';
    }
}