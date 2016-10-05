<?php
/*
 Copyright Game Server Services, Inc.

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

 http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */

namespace GS2\Core;

use GuzzleHttp\Client as Client;
use GuzzleHttp\Exception\RequestException as RequestException;

use GS2\Core\Gs2Credentials as Gs2Credentials;
use GS2\Core\Exception\BadRequestException as BadRequestException;
use GS2\Core\Exception\BadGatewayException as BadGatewayException;
use GS2\Core\Exception\ConflictException as ConflictException;
use GS2\Core\Exception\UnauthorizedException as UnauthorizedException;
use GS2\Core\Exception\QuotaExceedException as QuotaExceedException;
use GS2\Core\Exception\NotFoundException as NotFoundException;
use GS2\Core\Exception\InternalServerErrorException as InternalServerErrorException;
use GS2\Core\Exception\ServiceUnavailableException as ServiceUnavailableException;
use GS2\Core\Exception\RequestTimeoutException as RequestTimeoutException;
use GS2\Core\Exception\NullPointerException as NullPointerException;

/**
 * APIクライアントの基底クラス
 * 
 * @author Game Server Services, inc. <contact@gs2.io>
 * @copyright Game Server Services, Inc.
 */
abstract class AbstractGs2Client {
	
	const ENDPOINT_HOST = 'https://{service}.{region}.gs2.io';

	/**
	 * コンストラクタ。
	 * 
	 * @param string $region リージョン名
	 * @param Gs2Credentials $credentials 認証情報
	 * @param array $options オプション
	 */
	public function __construct($region, Gs2Credentials $credentials, &$options = []) {
		$this->region = $region;
		$this->credentials = $credentials;
		$this->params = $options;
	}
	
	/**
	 * 署名を作成
	 * 
	 * @param string $module アクセス対象モジュール
	 * @param string $function アクセス対象関数
	 * @param integer $timestamp タイムスタンプ
	 * @return string 署名
	 */
	private function createSign($module, $function, $timestamp) {
		return base64_encode(hash_hmac('sha256', $module. ':'. $function. ':'. $timestamp, base64_decode($this->credentials->getClientSecret()), true));
	}
	
	/**
	 * GET リクエストを発行
	 * 
	 * @param string $module アクセス対象モジュール
	 * @param string $function アクセス対象関数
	 * @param string $endpoint アクセス先サブドメイン
	 * @param string $path アクセス先パス
	 * @param array $query クエリストリング
	 * @param array $extparams 拡張オプション
	 * @return array 応答内容(JSON)
	 * 
	 * @throws BadRequestException リクエストパラメータが不正な場合にスローされます
	 */
	protected function doGet($module, $function, $endpoint, $path, array &$query = [], array &$extparams = []) {
		$host = str_replace('{service}', $endpoint, str_replace('{region}', $this->region, AbstractGs2Client::ENDPOINT_HOST));
		$params = $this->params;
		$params += $extparams;
		$params += ['timeout' => 60];
		$timestamp = time();
		$sign = $this->createSign($module, $function, $timestamp);
		$header = [
				'X-GS2-CLIENT-ID' => $this->credentials->getClientId(),
				'X-GS2-REQUEST-TIMESTAMP' => $timestamp,
				'X-GS2-REQUEST-SIGN' => $sign
		];
		if(isset($params['headers'])) {
			$params['headers'] = array_merge($params['headers'], $header);
		} else {
			$params += ['headers' => $header];
		}
		$params += ['query' => $query];
		$client = new Client(['base_uri' => $host]);
		try {
			$response = $client->get($host. $path, $params);
			if(is_null($response)) {
				var_dump($params);
				print($path);
			}
			return $this->doHandling($response);
		} catch(RequestException $e) {
			if(is_null($e->getResponse())) {
				throw new InternalServerErrorException($e);
			} else {
				return $this->doHandling($e->getResponse());
			}
		}
	}

	/**
	 * POST リクエストを発行
	 *
	 * @param string $module アクセス対象モジュール
	 * @param string $function アクセス対象関数
	 * @param string $endpoint アクセス先サブドメイン
	 * @param string $path アクセス先パス
	 * @param string $body リクエストボディ
	 * @param array $query クエリストリング
	 * @param array $extparams 拡張オプション
	 * @return array 応答内容(JSON)
	 *
	 * @throws BadRequestException リクエストパラメータが不正な場合にスローされます
	 */
	protected function doPost($module, $function, $endpoint, $path, $body, array &$query = [], array &$extparams = []) {
		if(is_null($body)) throw new NullPointerException();
		$host = str_replace('{service}', $endpoint, str_replace('{region}', $this->region, AbstractGs2Client::ENDPOINT_HOST));
		$params = $this->params;
		$params += $extparams;
		$params += ['timeout' => 60];
		$timestamp = time();
		$sign = $this->createSign($module, $function, $timestamp);
		$header = [
				'X-GS2-CLIENT-ID' => $this->credentials->getClientId(),
				'X-GS2-REQUEST-TIMESTAMP' => $timestamp,
				'X-GS2-REQUEST-SIGN' => $sign
		];
		if(isset($params['headers'])) {
			$params['headers'] = array_merge($params['headers'], $header);
		} else {
			$params += ['headers' => $header];
		}
		$params += ['query' => $query];
		$params += ['json' => $body];
		$client = new Client(['base_uri' => $host]);
		try {
			$response = $client->post($host. $path, $params);
			if(is_null($response)) {
				var_dump($params);
				print($path);
			}
			return $this->doHandling($response);
		} catch(RequestException $e) {
			if(is_null($e->getResponse())) {
				throw new InternalServerErrorException($e);
			} else {
				return $this->doHandling($e->getResponse());
			}
		}
	}

	/**
	 * PUT リクエストを発行
	 *
	 * @param string $module アクセス対象モジュール
	 * @param string $function アクセス対象関数
	 * @param string $endpoint アクセス先サブドメイン
	 * @param string $path アクセス先パス
	 * @param string $body リクエストボディ
	 * @param array $query クエリストリング
	 * @param array $extparams 拡張オプション
	 * @return array 応答内容(JSON)
	 *
	 * @throws BadRequestException リクエストパラメータが不正な場合にスローされます
	 */
	protected function doPut($module, $function, $endpoint, $path, $body, array &$query = [], array &$extparams = []) {
		if(is_null($body)) throw new NullPointerException();
		$host = str_replace('{service}', $endpoint, str_replace('{region}', $this->region, AbstractGs2Client::ENDPOINT_HOST));
		$params = $this->params;
		$params += $extparams;
		$params += ['timeout' => 60];
		$timestamp = time();
		$sign = $this->createSign($module, $function, $timestamp);
		$header = [
				'X-GS2-CLIENT-ID' => $this->credentials->getClientId(),
				'X-GS2-REQUEST-TIMESTAMP' => $timestamp,
				'X-GS2-REQUEST-SIGN' => $sign
		];
		if(isset($params['headers'])) {
			$params['headers'] = array_merge($params['headers'], $header);
		} else {
			$params += ['headers' => $header];
		}
		$params += ['query' => $query];
		$params += ['json' => $body];
		$client = new Client(['base_uri' => $host]);
		try {
			$response = $client->put($host. $path, $params);
			if(is_null($response)) {
				var_dump($params);
				print($path);
			}
			return $this->doHandling($response);
		} catch(RequestException $e) {
			if(is_null($e->getResponse())) {
				throw new InternalServerErrorException($e);
			} else {
				return $this->doHandling($e->getResponse());
			}
		}
	}
	
	/**
	 * DELETE リクエストを発行
	 *
	 * @param string $module アクセス対象モジュール
	 * @param string $function アクセス対象関数
	 * @param string $endpoint アクセス先サブドメイン
	 * @param string $path アクセス先パス
	 * @param array $query クエリストリング
	 * @param array $extparams 拡張オプション
	 * @return array 応答内容(JSON)
	 *
	 * @throws BadRequestException リクエストパラメータが不正な場合にスローされます
	 */
	protected function doDelete($module, $function, $endpoint, $path, array &$query = [], array &$extparams = []) {
		$host = str_replace('{service}', $endpoint, str_replace('{region}', $this->region, AbstractGs2Client::ENDPOINT_HOST));
		$params = $this->params;
		$params += $extparams;
		$params += ['timeout' => 60];
		$timestamp = time();
		$sign = $this->createSign($module, $function, $timestamp);
		$header = [
				'X-GS2-CLIENT-ID' => $this->credentials->getClientId(),
				'X-GS2-REQUEST-TIMESTAMP' => $timestamp,
				'X-GS2-REQUEST-SIGN' => $sign
		];
		if(isset($params['headers'])) {
			$params['headers'] = array_merge($params['headers'], $header);
		} else {
			$params += ['headers' => $header];
		}
		$params += ['query' => $query];
		$client = new Client(['base_uri' => $host]);
		try {
			$response = $client->delete($host. $path, $params);
			if(is_null($response)) {
				var_dump($params);
				print($path);
			}
			return $this->doHandling($response);
		} catch(RequestException $e) {
			if(is_null($e->getResponse())) {
				throw new InternalServerErrorException($e);
			} else {
				return $this->doHandling($e->getResponse());
			}
		}
	}
	
	/**
	 * レスポンスをパースする
	 * 
	 * @param unknown $response レスポンス
	 * @throws BadRequestException リクエストパラメータが不正な場合にスローされます
	 * @return array 応答内容(JSON)
	 */
	private function doHandling($response) {
		$statusCode = $response->getStatusCode();
		$body = $response->getBody(true);
		switch($statusCode) {
			case 200: return json_decode($body, true);
			case 400: throw new BadRequestException(json_decode(json_decode($body, true)['message'], true));
			case 401: throw new UnauthorizedException(json_decode(json_decode($body, true)['message'], true));
			case 402: throw new QuotaExceedException(json_decode(json_decode($body, true)['message'], true));
			case 404: throw new NotFoundException(json_decode(json_decode($body, true)['message'], true));
			case 409: throw new ConflictException(json_decode(json_decode($body, true)['message'], true));
			case 500: throw new InternalServerErrorException(json_decode(json_decode($body, true)['message'], true));
			case 502: throw new BadGatewayException(json_decode(json_decode($body, true)['message'], true));
			case 503: throw new ServiceUnavailableException(json_decode(json_decode($body, true)['message'], true));
			case 504: throw new RequestTimeoutException(json_decode(json_decode($body, true)['message'], true));
		}
		print $statusCode;
		print $body;
	}
}