<?php


namespace Romero\PerfectMoney;

use Exception;

class PerfectMoney
{

    /**
     * @var string
     */
    protected $account_id;

    /**
     * @var string
     */
    protected $passphrase;

    /**
     * @var string
     */
    protected $alt_passphrase;

    /**
     * @var string
     */
    protected $marchant_name;

    /**
     * @var string
     */
    protected $marchant_id;
    protected $payment_url;
    protected $payment_url_method;
    protected $nopayment_url;
    protected $nopayment_url_method;
    protected $status_url;
    protected $suggested_memo;
    protected $units;
    protected $url;

    /**
     * @var array
     */
    protected $ssl_fix = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]];
    public function __construct()
    {
        $this->account_id = config('perfectmoney.account_id');
        $this->marchant_name = config('perfectmoney.marchant_name');
        $this->passphrase = config('perfectmoney.passphrase');
        $this->alt_passphrase = config('perfectmoney.alternate_passphrase');
        $this->marchant_id = config('perfectmoney.marchant_id');
        $this->payment_url = config('perfectmoney.payment_url');
        $this->nopayment_url = config('perfectmoney.nopayment_url');
        $this->payment_url_method = config('perfectmoney.payment_url_method');
        $this->nopayment_url_method = config('perfectmoney.nopayment_url_method');
        $this->status_url = config('perfectmoney.status_url');
        $this->suggested_memo = config('perfectmoney.suggested_memo');
        $this->units = config('perfectmoney.units');
        $this->url = config('perfectmoney.url');
    }



    /**
     * Fetch the public name of another existing PerfectMoney account
     *
     */
    public function getAccountName($account)
    {
        // trying to open URL to process PerfectMoney getAccountName request
        $data = file_get_contents("https://perfectmoney.com/acct/acc_name.asp?AccountID={$this->account_id}&PassPhrase={$this->passphrase}&Account={$account}"
        ,false, stream_context_create($this->ssl_fix)
        );

        if($data == 'ERROR: Can not login with passed AccountID and PassPhrase'){

            throw new Exception('Invalid PerfectMoney Username or Password.', 500);

        }elseif($data == 'ERROR: Invalid Account'){

            throw new Exception('Invalid PerfectMoney Account specified.', 500);

        }

        return $data;
    }


    /**
     * get the balance for the wallet or a specific account inside a wallet
     *
     */
    public function getBalance()
    {
        // trying to open URL to process PerfectMoney Balance request
        $data = file_get_contents("https://perfectmoney.com/acct/balance.asp?AccountID={$this->account_id}&PassPhrase={$this->passphrase}"
            ,false, stream_context_create($this->ssl_fix)
        );

        // searching for hidden fields
        if (!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $data, $result, PREG_SET_ORDER)) {
            return false;
        }

        // putting data to array
        $array = [];

        foreach ($result as $item) {
            $array[$item[1]] = $item[2];
        }



        return $array[$this->marchant_id] ?? false;
    }


    /**
     * Transfer funds(currency) to another existing PerfectMoney account
     *
     */
    public function transferFund($fromAccount, $toAccount, $amount, $paymentID = null, $memo = null)
    {
        $urlString = "https://perfectmoney.com/acct/confirm.asp?AccountID={$this->account_id}&PassPhrase={$this->passphrase}&Payer_Account={$fromAccount}&Payee_Account={$toAccount}&Amount={$amount}&PAY_IN=1";

        $urlString .= ($paymentID != null) ? "&PAYMENT_ID={$paymentID}" : "";

        $urlString .= ($paymentID != null) ? "&Memo={$memo}" : "";

        // trying to open URL to process PerfectMoney Balance request
        $data = file_get_contents($urlString, false, stream_context_create($this->ssl_fix));

        // searching for hidden fields
        if (!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $data, $result, PREG_SET_ORDER)) {
            return false;
        }

        // putting data to array
        $array = [];

        foreach ($result as $item) {
            $array[$item[1]] = $item[2];
        }

        return $array;
    }


    /**
     * Create new E-Voucher with your PerfectMoney account
     *
     */
    public function createEV($payerAccount, $amount)
    {
        // trying to open URL to process PerfectMoney Balance request
        $data = file_get_contents("https://perfectmoney.com/acct/ev_create.asp?AccountID={$this->account_id}&PassPhrase={$this->passphrase}&Payer_Account={$payerAccount}&Amount={$amount}", false, stream_context_create($this->ssl_fix));

        // searching for hidden fields
        if (!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $data, $result, PREG_SET_ORDER)) {
            return false;
        }

        // putting data to array
        $array = [];

        foreach ($result as $item) {
            $array[$item[1]] = $item[2];
        }

        return $array;
    }

    public function transferEV($toAccount, $EVnumber, $EVactivationCode)
    {
        // trying to open URL to process PerfectMoney Balance request
        $data = file_get_contents("https://perfectmoney.com/acct/ev_activate.asp?AccountID={$this->account_id}&PassPhrase={$this->passphrase}&Payee_Account={$toAccount}&ev_number={$EVnumber}&ev_code={$EVactivationCode}", false, stream_context_create($this->ssl_fix));

        // searching for hidden fields
        if (!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $data, $result, PREG_SET_ORDER)) {
            return false;
        }

        // putting data to array
        $array = [];

        foreach ($result as $item) {
            $array[$item[1]] = $item[2];
        }

        return $array;
    }

    public function submitForm($data = [])
    {
        ?>
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <script type="text/javascript">
                function closethisasap() {
                    document.forms["redirectpost"].submit();
                }
            </script>
        </head>
        <body onload="closethisasap();">
        <form name="redirectpost" method="post" action="https://perfectmoney.com/api/step1.asp">
            <?php
            echo '<input type="hidden" name="PAYEE_ACCOUNT" value="' . $this->marchant_id . '"> ';
            echo '<input type="hidden" name="PAYEE_NAME" value="' . $this->marchant_name . '"> ';
            echo '<input type="hidden" name="PAYMENT_ID" value="' . $data['payment_id'] . '"> ';
            echo '<input type="hidden" name="PAYMENT_AMOUNT" value="' . $data['payment_amount'] . '"> ';
            echo '<input type="hidden" name="PAYMENT_UNITS" value="' . $this->units . '"> ';
            echo '<input type="hidden" name="STATUS_URL" value="' . $this->status_url . '"> ';
            echo '<input type="hidden" name="PAYMENT_URL" value="' . $this->payment_url . '"> ';
            echo '<input type="hidden" name="PAYMENT_URL_METHOD" value="' . $this->payment_url_method . '"> ';
            echo '<input type="hidden" name="NOPAYMENT_URL" value="' . $this->nopayment_url . '"> ';
            echo '<input type="hidden" name="NOPAYMENT_URL_METHOD" value="' . $this->nopayment_url_method . '"> ';
            echo '<input type="hidden" name="SUGGESTED_MEMO" value="' . $this->suggested_memo . '"> ';
            ?>
        </form>
        </body>
        </html>
        <?php
        exit;
    }
}
