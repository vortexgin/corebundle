<?php
namespace Vortexgin\CoreBundle\Twig;

class VortexginExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'stripslashes' => new \Twig_Filter_Method($this, 'customStripslashes'),
        );
    }

    public function customStripslashes($string)
    {
        return stripslashes($string);
    }

    public function getName()
    {
        return 'vortexgin_extension';
    }
}
?>
