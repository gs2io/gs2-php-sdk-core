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

namespace GS2\Core\Exception;

/**
 * UnauthorizedException(401) エラーを表現するためのクラス
 * 
 * @author Game Server Services, inc. <contact@gs2.io>
 * @copyright Game Server Services, Inc.
 *
 */
class UnauthorizedException extends \Exception {
	
	/**
	 * コンストラクタ
	 * 
	 * @param unknown $errors エラーリスト
	 */
	public function __construct($errors) {
		parent::__construct(json_encode($errors));
		$this->errors = $errors;
	}
	
	/**
	 * エラーリストを取得する
	 * 
	 * @return エラーリスト
	 */
	public function getErrors() {
		return $this->errors;
	}
	
	/**
	 * {@inheritDoc}
	 * @see Exception::__toString()
	 */
	public function __toString() {
		return json_encode($this->errors);
	}
}