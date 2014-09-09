<?php

namespace CanalTP\SamCoreBundle\Twig;

/**
 * @author Kévin Ziemianski <kevin.ziemianski@canaltp.fr>
 */
class TokenExtension extends \Twig_Extension
{
    protected $tokenManager;
    
    public function __construct($tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }
    
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('isTokenAllow', array($this, 'isTokenAllow'))
        );
    }
    
    public function isTokenAllow($token, $perimeters)
    {
        foreach ($perimeters as $perimeter) {
            if (!$this->tokenManager->checkAllowedToNetworkAction($perimeter->getExternalCoverageId(), $perimeter->getExternalNetworkId(), $token)) {
                return false;
            }
        }
        
        return true;
    }
    
    public function getName()
    {
        return 'token';
    }

}
