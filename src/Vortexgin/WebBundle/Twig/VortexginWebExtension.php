<?php

namespace Vortexgin\WebBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Vortexgin\LibraryBundle\Manager\FormTokenizerManager;
use Vortexgin\LibraryBundle\Utils\StringUtils;

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
            new TwigFilter('truncate_content', array($this, 'truncateContent')),
            new TwigFilter('truncate_word', array($this, 'truncateWord')),
            new TwigFilter('timeago', array($this, 'timeAgo')),
            new TwigFilter('slugify', array($this, 'slugify')),
            new TwigFilter('masking_string', array($this, 'maskingString')),
            new TwigFilter('masking_email', array($this, 'maskingEmail')),
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
     * Twig Filter Truncate Content
     * 
     * @param string  $str      String to truncate
     * @param int     $len      Length
     * @param boolean $readMore Using read more?
     * @param string  $url      Read more link
     * @param string  $target   Link target
     * 
     * @return string
     */
    public function truncateContent($str, $len, $readMore = false, $url = null, $target = null)
    {
        if (strlen($str) > $len) {
            $str = substr($str, 0, $len);
            $str .= '...';
        }
        if ($readMore === true) {
            if ($target != null) {
                $target = sprintf('target="%s"', $target);
            } else {
                $target = '';
            }
            $str = sprintf('%s <a href="%s" %s>read more</a>', $str, $url, $target);
        }
        return $str;
    }

    /**
     * Twig Filter Truncate Word
     * 
     * @param string  $str      String to truncate
     * @param int     $len      Length
     * @param boolean $readMore Using read more?
     * @param string  $url      Read more link
     * @param string  $target   Link target
     * 
     * @return string
     */
    public function truncateWord($str, $count, $readMore = false, $url = null, $target = null)
    {
        $exp = explode(' ', $str);
        if (count($exp) > $count) {
            $tmp = [];
            foreach ($exp as $index=>$word) {
                if ($index >= $count) {
                    break;
                }
                $tmp[] = $word;
            }
            $str = sprintf('%s...', implode(' ', $tmp));
        }
        if ($readMore === true) {
            if ($target != null) {
                $target = sprintf('target="%s"', $target);
            } else {
                $target = '';
            }
            $str = sprintf('%s <a href="%s" %s>read more</a>', $str, $url, $target);
        }
        return $str;
    }

    /**
     * Twig Filter Time Ago
     * 
     * @param mixed $date Date
     * 
     * @return string
     */
    public function timeAgo($date)
    {
        if ($date instanceof \DateTime) {
            $date = $date->format('Y-m-d G:i:s');
        }

        $time = time() - strtotime($date); 

        $units = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($units as $unit => $val) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return ($val == 'second')? 'a few seconds ago' : 
                (($numberOfUnits>1) ? $numberOfUnits : 'a')
                .' '.$val.(($numberOfUnits>1) ? 's' : '').' ago';
        }
    }

    /**
     * Slugify
     * 
     * @return string
     */
    public function slugify($string, $separator = '-')
    {
        return StringUtils::createSlug($string, $separator);
    }

    /**
     * Masking String
     * 
     * @return string
     */
    public function maskingString($string, $len = 3)
    {
        return StringUtils::maskingString($string, $len);
    }

    /**
     * Masking Email
     * 
     * @return string
     */
    public function maskingEmail($string, $len = 3)
    {
        return StringUtils::maskingEmail($string, $len);
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