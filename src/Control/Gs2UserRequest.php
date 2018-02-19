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

namespace Gs2\Control;

class Gs2UserRequest extends Gs2BasicRequest
{
    /** @var string アクセストークン */
    private $accessToken;

    /**
     * アクセストークンを取得。
     *
     * @return string アクセストークン
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * アクセストークンを設定。
     * 通常は自動的に計算されるため、この値を設定する必要はありません。
     *
     * @param string $accessToken アクセストークン
     */
    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * アクセストークンを設定。
     * 通常は自動的に計算されるため、この値を設定する必要はありません。
     *
     * @param string $accessToken アクセストークン
     * @return self
     */
    public function withAccessToken(string $accessToken): self
    {
        $this->setAccessToken($accessToken);
        return $this;
    }
}