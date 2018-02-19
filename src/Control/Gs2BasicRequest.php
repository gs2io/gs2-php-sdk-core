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

abstract class Gs2BasicRequest
{
    /** @var string GS2認証クライアントID */
    private $xGs2ClientId;
    /** @var string タイムスタンプ */
    private $xGs2Timestamp;
    /** @var int GS2認証署名 */
    private $xGs2RequestSign;
    /** @var string GS2リクエストID */
    private $xGs2RequestId;

    /**
     * GS2認証クライアントIDを取得。
     *
     * @return string GS2認証クライアントID
     */
    public function getxGs2ClientId(): string
    {
        return $this->xGs2ClientId;
    }

    /**
     * GS2認証クライアントIDを設定。
     * 通常は自動的に計算されるため、この値を設定する必要はありません。
     *
     * @param string $xGs2ClientId GS2認証クライアントID
     */
    public function setxGs2ClientId(string $xGs2ClientId)
    {
        $this->xGs2ClientId = $xGs2ClientId;
    }

    /**
     * GS2認証クライアントIDを設定。
     * 通常は自動的に計算されるため、この値を設定する必要はありません。
     *
     * @param string $xGs2ClientId GS2認証クライアントID
     * @return self
     */
    public function withxGs2ClientId(string $xGs2ClientId): self
    {
        $this->setxGs2ClientId($xGs2ClientId);
        return $this;
    }

    /**
     * タイムスタンプを取得。
     *
     * @return int タイムスタンプ
     */
    public function getxGs2Timestamp()
    {
        return $this->xGs2Timestamp;
    }

    /**
     * タイムスタンプを設定。
     * 通常は自動的に計算されるため、この値を設定する必要はありません。
     *
     * @param int $xGs2Timestamp タイムスタンプ
     */
    public function setxGs2Timestamp(int $xGs2Timestamp)
    {
        $this->xGs2Timestamp = $xGs2Timestamp;
    }

    /**
     * タイムスタンプを設定。
     * 通常は自動的に計算されるため、この値を設定する必要はありません。
     *
     * @param int $xGs2Timestamp タイムスタンプ
     * @return self
     */
    public function withxGs2Timestamp(int $xGs2Timestamp): self
    {
        $this->setxGs2Timestamp($xGs2Timestamp);
        return $this;
    }

    /**
     * GS2認証署名を取得。
     *
     * @return string GS2認証署名
     */
    public function getxGs2RequestSign(): string
    {
        return $this->xGs2RequestSign;
    }

    /**
     * GS2認証署名を設定。
     * 通常は自動的に計算されるため、この値を設定する必要はありません。
     *
     * @param string $xGs2RequestSign GS2認証署名
     */
    public function setxGs2RequestSign(string $xGs2RequestSign)
    {
        $this->xGs2RequestSign = $xGs2RequestSign;
    }

    /**
     * GS2認証署名を設定。
     * 通常は自動的に計算されるため、この値を設定する必要はありません。
     *
     * @param string $xGs2RequestSign GS2認証署名
     * @return self
     */
    public function withxGs2RequestSign(string $xGs2RequestSign): self
    {
        $this->setxGs2RequestSign($xGs2RequestSign);
        return $this;
    }

    /**
     * GS2リクエストIDを取得。
     *
     * @return string GS2リクエストID
     */
    public function getRequestId()
    {
        return $this->xGs2RequestId;
    }

    /**
     * GS2リクエストIDを設定。
     *
     * @param string $xGs2RequestId GS2リクエストID
     */
    public function setRequestId(string $xGs2RequestId)
    {
        $this->xGs2RequestId = $xGs2RequestId;
    }

    /**
     * GS2リクエストIDを設定。
     *
     * @param string $xGs2RequestId GS2リクエストID
     * @return self
     */
    public function withRequestId(string $xGs2RequestId): self
    {
        $this->setRequestId($xGs2RequestId);
        return $this;
    }
}