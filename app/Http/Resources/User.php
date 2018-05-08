<?php namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Tymon\JWTAuth\Facades\JWTFactory;

/**
 * Class User
 * @package App\Http\Resources
 */
class User extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'access_token' => $this->access_token,
            'token_type' => 'bearer',
            'expires_in' => JWTFactory::getTTL() * 60
        ];
    }
}
