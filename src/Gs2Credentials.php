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

/**
 * 認証情報
 * 
 * @author Game Server Services, inc. <contact@gs2.io>
 * @copyright Game Server Services, Inc.
 *
 */
class Gs2Credentials {

	/**
	 * コンストラクタ
	 * 
	 * @param string $clientId GSIのアクセスキー
	 * @param string $clientSecret GSIのシークレット
	 */
	public function __construct($clientId, $clientSecret) {
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
	}

	/**
	 * GSIのアクセスキー を取得
	 * 
	 * @return string GSIのアクセスキー
	 */
	public function getClientId() {
		return $this->clientId;
	}

	/**
	 * GSIのシークレット を取得
	 * 
	 * @return string GSIのシークレット
	 */
	public function getClientSecret() {
		return $this->clientSecret;
	}
}