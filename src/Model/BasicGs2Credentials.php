<?php
/*
 * Copyright 2016-2018 Game Server Services, Inc. or its affiliates. All Rights
 * Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Gs2\Core\Model;

/**
 * 認証情報
 * 
 * @author Game Server Services, inc. <contact@gs2.io>
 * @copyright Game Server Services, Inc.
 *
 */
class BasicGs2Credentials implements IGs2Credential {

    /**
     * @var string GSIのアクセスキー
     */
    private $clientId;

    /**
     * @var string GSIのシークレット
     */
    private $clientSecret;

	/**
	 * コンストラクタ
	 * 
	 * @param string $clientId GSIのアクセスキー
	 * @param string $clientSecret GSIのシークレット
	 */
	public function __construct(string $clientId, string $clientSecret)
    {
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
	}

	/**
	 * GSIのアクセスキー を取得
	 * 
	 * @return string GSIのアクセスキー
	 */
	public function getClientId(): string
    {
		return $this->clientId;
	}

	/**
	 * GSIのシークレット を取得
	 * 
	 * @return string GSIのシークレット
	 */
	public function getClientSecret(): string
    {
		return $this->clientSecret;
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
                base64_decode($this->getClientSecret()),
                true
            )
        );
    }

    /**
     * 認証処理
     * @param string $module モジュール名
     * @param string $function ファンクション名
     * @param int $timestamp タイムスタンプ
     * @return array
     */
    public function authorized(
        string $module,
        string $function,
        int $timestamp): array
    {
        $sign = $this->createSign($module, $function, $timestamp);
        return [
            'X-GS2-CLIENT-ID' => $this->getClientId(),
            'X-GS2-REQUEST-TIMESTAMP' => $timestamp,
            'X-GS2-REQUEST-SIGN' => $sign
        ];
    }
}