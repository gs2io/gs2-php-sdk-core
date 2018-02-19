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

namespace Gs2\Core;

use Gs2\Core\Exception\BadGatewayException as BadGatewayException;
use Gs2\Core\Exception\BadRequestException as BadRequestException;
use Gs2\Core\Exception\ConflictException as ConflictException;
use Gs2\Core\Exception\InternalServerErrorException as InternalServerErrorException;
use Gs2\Core\Exception\NotFoundException as NotFoundException;
use Gs2\Core\Exception\NullPointerException as NullPointerException;
use Gs2\Core\Exception\QuotaExceedException as QuotaExceedException;
use Gs2\Core\Exception\RequestTimeoutException as RequestTimeoutException;
use Gs2\Core\Exception\ServiceUnavailableException as ServiceUnavailableException;
use Gs2\Core\Exception\UnauthorizedException as UnauthorizedException;
use Gs2\Core\Model\IGs2Credential;
use Gs2\Core\Model\Region;
use GuzzleHttp\Client as Client;
use GuzzleHttp\Exception\RequestException as RequestException;
use GuzzleHttp\Message\ResponseInterface;

/**
 * APIクライアントの基底クラス
 * 
 * @author Game Server Services, inc. <contact@gs2.io>
 * @copyright Game Server Services, Inc.
 */
abstract class AbstractGs2Client {
	
	const ENDPOINT_HOST = 'https://{service}.{region}.gs2io.com';

    /**
     * @var string
     */
	private $region;

    /**
     * @var IGs2Credential
     */
	private $credentials;

    /**
     * @var array
     */
	private $options;

	/**
	 * コンストラクタ。
	 * 
	 * @param IGs2Credential $credentials 認証情報
	 * @param array $options オプション
	 */
	public function __construct(
        IGs2Credential $credentials,
        array &$options = []
    ) {
		$this->region = Region::AP_NORTHEAST_1;
		$this->credentials = $credentials;
		$this->options = $options;
	}

    /**
     * アクセス先リージョンを取得
     *
     * @return string アクセス先リージョン
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * アクセス先リージョンを設定
     *
     * @param string $region アクセス先リージョン
     */
    public function setRegion(string $region)
    {
        $this->region = $region;
    }

    /**
     * アクセス先リージョンを設定
     *
     * @param string $region アクセス先リージョン
     * @return self
     */
    public function withRegion(string $region): self
    {
        $this->setRegion($region);
        return $this;
	}

	/**
	 * 署名を作成
	 * 
	 * @param string $module アクセス対象モジュール
	 * @param string $function アクセス対象関数
	 * @param integer $timestamp タイムスタンプ
	 * @return string 署名
	 */
	private function createSign(string $module, string $function, int $timestamp)
    {
		return base64_encode(
		    hash_hmac(
		        'sha256',
                $module. ':'. $function. ':'. $timestamp,
                base64_decode($this->credentials->getClientSecret()),
                true
            )
        );
	}
	
	/**
	 * GET リクエストを発行
	 * 
	 * @param string $module アクセス対象モジュール
	 * @param string $function アクセス対象関数
	 * @param string $endpoint アクセス先サブドメイン
	 * @param string $path アクセス先パス
	 * @param array $query クエリストリング
	 * @param array $alternativeParams 拡張オプション
	 * @return array 応答内容(JSON)
     * @throws InternalServerErrorException
	 */
	protected function doGet(
	    string $module,
        string $function,
        string $endpoint,
        string $path,
        array &$query = [],
        array &$alternativeParams = []
    ) {
		$host = str_replace('{service}', $endpoint, str_replace('{region}', $this->region, AbstractGs2Client::ENDPOINT_HOST));
		$params = $this->options;
		$params += $alternativeParams;
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
            /** @var ResponseInterface $response */
			$response = $client->get($host. $path, $params);
			if(is_null($response)) {
				var_dump($params);
				print($path);
			}
			return $this->doHandling($response);
		} catch(RequestException $e) {
			if(is_null($e->getResponse())) {
                throw new InternalServerErrorException([
                    'message' => $e->getMessage()
                ]);
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
	 * @param array $alternativeParams 拡張オプション
	 * @return array 応答内容(JSON)
     * @throws InternalServerErrorException
     * @throws NullPointerException
	 */
	protected function doPost(
	    string $module,
        string $function,
        string $endpoint,
        string $path,
        string $body,
        array &$query = [],
        array &$alternativeParams = []
    ) {
		if(is_null($body)) throw new NullPointerException();
		$host = str_replace('{service}', $endpoint, str_replace('{region}', $this->region, AbstractGs2Client::ENDPOINT_HOST));
		$params = $this->options;
		$params += $alternativeParams;
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
		    /** @var ResponseInterface $response */
			$response = $client->post($host. $path, $params);
			if(is_null($response)) {
				var_dump($params);
				print($path);
			}
			return $this->doHandling($response);
		} catch(RequestException $e) {
			if(is_null($e->getResponse())) {
                throw new InternalServerErrorException([
                    'message' => $e->getMessage()
                ]);
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
	 * @param array $alternativeParams 拡張オプション
	 * @return array 応答内容(JSON)
     * @throws InternalServerErrorException
     * @throws NullPointerException
	 */
	protected function doPut(
	    string $module,
        string $function,
        string $endpoint,
        string $path,
        string $body,
        array &$query = [],
        array &$alternativeParams = []
    ) {
		if(is_null($body)) throw new NullPointerException();
		$host = str_replace('{service}', $endpoint, str_replace('{region}', $this->region, AbstractGs2Client::ENDPOINT_HOST));
		$params = $this->options;
		$params += $alternativeParams;
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
            /** @var ResponseInterface $response */
			$response = $client->put($host. $path, $params);
			if(is_null($response)) {
				var_dump($params);
				print($path);
			}
			return $this->doHandling($response);
		} catch(RequestException $e) {
			if(is_null($e->getResponse())) {
                throw new InternalServerErrorException([
                    'message' => $e->getMessage()
                ]);
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
	 * @param array $alternativeParams 拡張オプション
	 * @return array 応答内容(JSON)
     * @throws InternalServerErrorException
	 */
	protected function doDelete(
	    string $module,
        string $function,
        string $endpoint,
        string $path,
        array &$query = [],
        array &$alternativeParams = []
    ) {
		$host = str_replace('{service}', $endpoint, str_replace('{region}', $this->region, AbstractGs2Client::ENDPOINT_HOST));
		$params = $this->options;
		$params += $alternativeParams;
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
            /** @var ResponseInterface $response */
			$response = $client->delete($host. $path, $params);
			if(is_null($response)) {
				var_dump($params);
				print($path);
			}
			return $this->doHandling($response);
		} catch(RequestException $e) {
			if(is_null($e->getResponse())) {
				throw new InternalServerErrorException([
				    'message' => $e->getMessage()
                ]);
			} else {
				return $this->doHandling($e->getResponse());
			}
		}
	}
	
	/**
	 * レスポンスをパースする
	 * 
	 * @param ResponseInterface $response レスポンス
	 * @throws BadRequestException リクエストパラメータが不正な場合にスローされます
	 * @return array 応答内容(JSON)
     * @throws BadGatewayException
     * @throws BadRequestException
     * @throws ConflictException
     * @throws InternalServerErrorException
     * @throws NotFoundException
     * @throws QuotaExceedException
     * @throws RequestTimeoutException
     * @throws ServiceUnavailableException
     * @throws UnauthorizedException
     */
	private function doHandling(ResponseInterface $response) {
		$statusCode = $response->getStatusCode();
		$body = $response->getBody();
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
        throw new InternalServerErrorException([
            'message' => "[$statusCode] unknown error"
        ]);
	}
}