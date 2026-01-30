<?php
$rawQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$query = preg_replace('/\s+/', '', $rawQuery);
$apiBase = 'https://apilist.tronscan.org';

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function format_trx($sun) {
    if ($sun === null || $sun === '') {
        return 'N/A';
    }
    $trx = (float) $sun / 1000000;
    return number_format($trx, 6, '.', '');
}

function format_time($ms) {
    if (!$ms) {
        return 'N/A';
    }
    $seconds = (int) round(((int) $ms) / 1000);
    return date('Y-m-d H:i:s', $seconds);
}

function fetch_json($url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_USERAGENT => 'ChainScan/1.0',
    ]);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        return [null, '请求失败：' . $error];
    }
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($status < 200 || $status >= 300) {
        return [null, '接口返回异常状态：' . $status];
    }
    $data = json_decode($response, true);
    if (!is_array($data)) {
        return [null, '解析数据失败。'];
    }
    return [$data, null];
}

function detect_type($query) {
    if ($query === '') {
        return ['type' => 'empty', 'label' => '请输入查询内容。'];
    }
    if (ctype_digit($query)) {
        return ['type' => 'block', 'label' => '区块高度'];
    }
    if (preg_match('/^[T][1-9A-HJ-NP-Za-km-z]{33}$/', $query)) {
        return ['type' => 'address', 'label' => '地址'];
    }
    if (preg_match('/^[a-fA-F0-9]{64}$/', $query)) {
        return ['type' => 'tx', 'label' => '交易哈希'];
    }
    return ['type' => 'unknown', 'label' => '无法识别的查询'];
}

$typeInfo = detect_type($query);
$type = $typeInfo['type'];
$message = '';
$payload = null;
$extra = [];

if ($type === 'address') {
    $accountEndpoint = $apiBase . '/api/account?address=' . urlencode($query);
    [$payload, $message] = fetch_json($accountEndpoint);

    if (!$message) {
        $txEndpoint = $apiBase . '/api/transaction?sort=-timestamp&count=true&limit=10&start=0&address=' . urlencode($query);
        [$txData, $txError] = fetch_json($txEndpoint);
        if ($txError) {
            $extra['txError'] = $txError;
        } else {
            $extra['transactions'] = $txData['data'] ?? [];
        }

        $trc20Endpoint = $apiBase . '/api/token_trc20/transfers?limit=10&start=0&sort=-timestamp&relatedAddress=' . urlencode($query);
        [$trc20Data, $trc20Error] = fetch_json($trc20Endpoint);
        if ($trc20Error) {
            $extra['trc20Error'] = $trc20Error;
        } else {
            $extra['trc20Transfers'] = $trc20Data['data'] ?? [];
        }
    }
} elseif ($type === 'tx') {
    $endpoint = $apiBase . '/api/transaction-info?hash=' . urlencode($query);
    [$payload, $message] = fetch_json($endpoint);
} elseif ($type === 'block') {
    $endpoint = $apiBase . '/api/block?number=' . urlencode($query);
    [$payload, $message] = fetch_json($endpoint);
} elseif ($type === 'empty') {
    $message = '请输入地址、交易哈希或区块高度。';
} else {
    $message = '未识别的查询内容，请确认输入是否正确。';
}

$usdtContract = 'TXLAQ63Xg1NAzckPwKHvzw7CSEmLMEqcdj';
?>
<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>查询结果 - USDT/TRX 交易查询</title>
    <meta
      name="description"
      content="查看 USDT/TRX 交易哈希、地址或区块高度查询结果。"
    />
    <link rel="stylesheet" href="styles.css" />
  </head>
  <body>
    <header class="site-header compact">
      <nav class="nav">
        <div class="logo">ChainScan</div>
        <div class="nav-links">
          <a href="index.php">首页</a>
          <a href="#result">查询结果</a>
        </div>
        <a class="cta" href="index.php#search">返回搜索</a>
      </nav>
      <section class="hero slim">
        <div class="hero-text">
          <p class="tag">TRON 网络 · 查询结果</p>
          <h1>查询：<?php echo h($rawQuery); ?></h1>
          <form class="search" role="search" action="search.php" method="get">
            <input
              type="search"
              name="q"
              placeholder="输入地址 / 交易哈希 / 区块高度"
              aria-label="搜索地址或交易哈希"
              value="<?php echo h($rawQuery); ?>"
              required
            />
            <button type="submit">查询</button>
          </form>
        </div>
      </section>
    </header>

    <main>
      <section id="result" class="section result">
        <div class="section-header">
          <h2>查询类型：<?php echo h($typeInfo['label']); ?></h2>
        </div>
        <?php if ($message): ?>
          <div class="notice warning">
            <strong>提示：</strong><?php echo h($message); ?>
          </div>
        <?php elseif (!$payload): ?>
          <div class="notice warning">
            <strong>提示：</strong>未查询到数据，请稍后重试。
          </div>
        <?php else: ?>
          <?php if ($type === 'address'): ?>
            <?php
            $tokenList = $payload['tokenBalances'] ?? [];
            $usdt = null;
            foreach ($tokenList as $token) {
                $tokenId = $token['tokenId'] ?? '';
                $abbr = strtoupper($token['tokenAbbr'] ?? $token['tokenName'] ?? '');
                if ($tokenId === $usdtContract || $abbr === 'USDT') {
                    $usdt = $token;
                    break;
                }
            }
            ?>
            <div class="card-grid">
              <article class="result-card">
                <h3>地址信息</h3>
                <p class="mono"><?php echo h($payload['address'] ?? $query); ?></p>
                <div class="metric">
                  <span>TRX 余额</span>
                  <strong><?php echo h(format_trx($payload['balance'] ?? null)); ?></strong>
                </div>
                <div class="metric">
                  <span>带宽</span>
                  <strong><?php echo h($payload['bandwidth'] ?? 'N/A'); ?></strong>
                </div>
                <div class="metric">
                  <span>能量</span>
                  <strong><?php echo h($payload['energy'] ?? 'N/A'); ?></strong>
                </div>
              </article>
              <article class="result-card">
                <h3>USDT(TRC20)</h3>
                <div class="metric">
                  <span>余额</span>
                  <strong><?php echo h($usdt['balance'] ?? 'N/A'); ?></strong>
                </div>
                <div class="metric">
                  <span>合约地址</span>
                  <strong class="mono"><?php echo h($usdt['tokenId'] ?? $usdtContract); ?></strong>
                </div>
                <p class="muted">数据来自 Tronscan API</p>
              </article>
            </div>

            <div class="result-table">
              <h3>TRX 交易记录</h3>
              <?php if (!empty($extra['txError'])): ?>
                <div class="notice warning">
                  <strong>提示：</strong><?php echo h($extra['txError']); ?>
                </div>
              <?php elseif (empty($extra['transactions'])): ?>
                <p class="muted">暂无 TRX 交易记录。</p>
              <?php else: ?>
                <div class="table">
                  <div class="table-row table-head">
                    <span>交易哈希</span>
                    <span>类型</span>
                    <span>金额</span>
                    <span>时间</span>
                    <span>状态</span>
                  </div>
                  <?php foreach ($extra['transactions'] as $tx): ?>
                    <div class="table-row">
                      <span class="hash"><?php echo h($tx['hash'] ?? ''); ?></span>
                      <span><?php echo h($tx['contractType'] ?? $tx['contract_type'] ?? 'TRX'); ?></span>
                      <span><?php echo h(format_trx($tx['amount'] ?? $tx['amount_str'] ?? null)); ?></span>
                      <span><?php echo h(format_time($tx['timestamp'] ?? null)); ?></span>
                      <span class="success"><?php echo h($tx['contractRet'] ?? 'SUCCESS'); ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="result-table">
              <h3>USDT/TRC20 转账记录</h3>
              <?php if (!empty($extra['trc20Error'])): ?>
                <div class="notice warning">
                  <strong>提示：</strong><?php echo h($extra['trc20Error']); ?>
                </div>
              <?php elseif (empty($extra['trc20Transfers'])): ?>
                <p class="muted">暂无 USDT 转账记录。</p>
              <?php else: ?>
                <div class="table">
                  <div class="table-row table-head">
                    <span>交易哈希</span>
                    <span>发送方</span>
                    <span>接收方</span>
                    <span>金额</span>
                    <span>时间</span>
                  </div>
                  <?php foreach ($extra['trc20Transfers'] as $transfer): ?>
                    <div class="table-row">
                      <span class="hash"><?php echo h($transfer['transaction_id'] ?? ''); ?></span>
                      <span class="mono"><?php echo h($transfer['from_address'] ?? ''); ?></span>
                      <span class="mono"><?php echo h($transfer['to_address'] ?? ''); ?></span>
                      <span><?php echo h($transfer['quant'] ?? 'N/A'); ?></span>
                      <span><?php echo h(format_time($transfer['block_timestamp'] ?? null)); ?></span>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          <?php elseif ($type === 'tx'): ?>
            <div class="card-grid">
              <article class="result-card">
                <h3>交易概览</h3>
                <p class="mono"><?php echo h($payload['hash'] ?? $query); ?></p>
                <div class="metric">
                  <span>区块</span>
                  <strong><?php echo h($payload['block'] ?? $payload['blockNumber'] ?? 'N/A'); ?></strong>
                </div>
                <div class="metric">
                  <span>状态</span>
                  <strong><?php echo h($payload['contractRet'] ?? $payload['contractRetDetail'] ?? 'N/A'); ?></strong>
                </div>
                <div class="metric">
                  <span>时间</span>
                  <strong><?php echo h(format_time($payload['timestamp'] ?? $payload['block_timestamp'] ?? null)); ?></strong>
                </div>
              </article>
              <article class="result-card">
                <h3>交易详情</h3>
                <div class="metric">
                  <span>发送方</span>
                  <strong class="mono"><?php echo h($payload['ownerAddress'] ?? $payload['owner_address'] ?? 'N/A'); ?></strong>
                </div>
                <div class="metric">
                  <span>接收方</span>
                  <strong class="mono"><?php echo h($payload['toAddress'] ?? $payload['to_address'] ?? 'N/A'); ?></strong>
                </div>
                <div class="metric">
                  <span>金额</span>
                  <strong><?php echo h($payload['amount'] ?? $payload['amount_str'] ?? 'N/A'); ?></strong>
                </div>
              </article>
            </div>
          <?php elseif ($type === 'block'): ?>
            <div class="card-grid">
              <article class="result-card">
                <h3>区块信息</h3>
                <div class="metric">
                  <span>区块高度</span>
                  <strong><?php echo h($payload['number'] ?? $query); ?></strong>
                </div>
                <div class="metric">
                  <span>区块哈希</span>
                  <strong class="mono"><?php echo h($payload['hash'] ?? 'N/A'); ?></strong>
                </div>
                <div class="metric">
                  <span>出块时间</span>
                  <strong><?php echo h(format_time($payload['timestamp'] ?? null)); ?></strong>
                </div>
                <div class="metric">
                  <span>交易数量</span>
                  <strong><?php echo h($payload['nrOfTrx'] ?? $payload['transactionCount'] ?? 'N/A'); ?></strong>
                </div>
              </article>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </section>
    </main>

    <footer class="footer">
      <div>
        <strong>ChainScan</strong>
        <p>专注 TRON 生态的链上交易与资产查询。</p>
      </div>
      <div>
        <p>数据来源：TRON 主网 / Tronscan API</p>
        <p>联系邮箱：support@example.com</p>
      </div>
    </footer>
  </body>
</html>
