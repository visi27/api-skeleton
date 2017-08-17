<?php

namespace AppBundle\Controller\Api;


use AppBundle\Api\ApiProblem;
use Symfony\Component\HttpFoundation\Request;

class ApiVersionController extends BaseController
{
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function handleVersionAction(Request $request)
    {
        // Get the requested path. The api/ portion is removed from the cath all route configuration
        $request_path = $request->get('path');

        // Get the configured values of Api versions. This will be used to try and fallback to an older API version
        $api_versions = $this->getParameter('api_versions');
        $latest_version = $this->getParameter('api_latest_version');

        // Extract requested API version from url
        $exploded_path = explode('/', $request_path);
        $url_api_version = $exploded_path[0];


        $router = $this->container->get('router');
        $matcher = $router->getMatcher();

        // Check if api url is starting with a version. If not try to redirect to latest api version
        $is_version = preg_match('/([vV])([0-9]{1,2})([.])([0-9]{1,2})[\/]/', $url_api_version.'/');
        if(!($is_version==1)){
            $url_api_version = $latest_version;

            // Add the latest version to the beginning of array of the original url
            array_unshift($exploded_path, $latest_version);

            // rebuild api url, use array_slice to get the exploded array of the original url without the original version
            $redirect_path = '/api/'.$latest_version.'/'.implode('/', array_slice($exploded_path, 1));

            // If the url mathces to a route, redirect. Otherwise we have already set $url_api_version to latest version
            // so it will continue to try and fallback to previeous verions until it finds one or no versions remain
            $matched = $matcher->match($redirect_path);
            if($matched["_route"] != "defaultCatchAll"){
                return $this->redirect($redirect_path, 307);
            }
        }

        $check_api_version = $url_api_version;
        // Check if the requested API version is correct (we have it in our config)
        // We loop the configured versions going back one version each time. For each version try and match the url to
        // a symfony route. If we have a match redirect to that route.
        while($check_api_version = $this->getPreviousVersion($check_api_version, $api_versions)){
            if($check_api_version){
                // rebuild api url, use array_slice to get the exploded array of the original url without the original version
                $redirect_path = '/api/'.$check_api_version.'/'.implode('/', array_slice($exploded_path, 1));
                $matched = $matcher->match($redirect_path);
                if($matched["_route"] != "defaultCatchAll"){
                    return $this->redirect($redirect_path, 307);
                }
            }else{
                // If the above conditions fail retur a 404 Not Found Response
                $apiProblem = new ApiProblem(404);
                $response = $this->get('api.response_factory')->createResponse($apiProblem);

                return $response;
            }
        }

        // If the above conditions fail retur a 404 Not Found Response
        $apiProblem = new ApiProblem(404);
        $response = $this->get('api.response_factory')->createResponse($apiProblem);

        return $response;

    }

    private function getPreviousVersion($currentVersion, $apiVersions){
        // Check if the requested API version is correct (we have it in our config)
        if (in_array($currentVersion, $apiVersions)) {
            // Get the index (on our config) of the requested API version.
            // If we find the version and it is not the first version try to fall back one version by redirecting
            $api_version_config_index = array_search($currentVersion, $apiVersions);
            if ($api_version_config_index && $api_version_config_index > 0) {
                // Build the path of this api for the previeous version
                $previeous_api_version = $apiVersions[$api_version_config_index - 1];

                return $previeous_api_version;
            }
        }

        return false;
    }
}