<?php
?>
<!DOCTYPE html>
<html lang="zh-CN">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>USDT/TRX 交易查询 | 链上地址与交易记录检索</title>
    <meta
      name="description"
      content="快速查询 USDT(TRC20) 与 TRX 交易记录、地址余额与合约信息。提供清晰的卡片布局、搜索入口与交易列表，体验类似 Tronscan 的浏览器界面。"
    />
    <link rel="canonical" href="https://example.com/index.php" />
    <meta property="og:title" content="USDT/TRX 交易查询" />
    <meta
      property="og:description"
      content="查询 USDT(TRC20) 与 TRX 交易记录、余额与地址详情。"
    />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://example.com/index.php" />
    <meta property="og:image" content="https://example.com/og-cover.png" />
    <meta name="twitter:card" content="summary_large_image" />
    <link rel="stylesheet" href="styles.css" />
    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "USDT/TRX 交易查询",
        "url": "https://example.com/",
        "potentialAction": {
          "@type": "SearchAction",
          "target": "https://example.com/search.php?q={search_term_string}",
          "query-input": "required name=search_term_string"
        }
      }
    </script>
  </head>
  <body>
    <header class="site-header">
      <nav class="nav">
        <div class="logo">ChainScan</div>
        <div class="nav-links">
          <a href="#features">功能</a>
          <a href="#tokens">资产</a>
          <a href="#transactions">交易</a>
          <a href="#faq">FAQ</a>
        </div>
        <a class="cta" href="#search">开始查询</a>
      </nav>
      <section class="hero" id="search">
        <div class="hero-text">
          <p class="tag">TRON 网络 · USDT/TRX 交易浏览</p>
          <h1>更快、更清晰的 USDT 与 TRX 交易查询体验</h1>
          <p class="subtitle">
            输入地址或交易哈希即可查看余额、交易状态与转账明细，布局参考
            Tronscan 的高效信息密度与卡片化展示。
          </p>
          <form class="search" role="search" action="search.php" method="get">
            <input
              type="search"
              name="q"
              placeholder="输入地址 / 交易哈希 / 区块高度"
              aria-label="搜索地址或交易哈希"
              required
            />
            <button type="submit">查询</button>
          </form>
          <div class="hero-stats">
            <div>
              <strong>8.2M</strong>
              <span>地址查询</span>
            </div>
            <div>
              <strong>1.1B</strong>
              <span>交易解析</span>
            </div>
            <div>
              <strong>99.99%</strong>
              <span>链上同步</span>
            </div>
          </div>
        </div>
        <div class="hero-card">
          <div class="card-header">
            <h2>最近交易</h2>
            <span class="status">同步中</span>
          </div>
          <ul class="tx-list">
            <li>
              <div>
                <p class="hash">4bce...8a1f</p>
                <span class="meta">USDT/TRC20 · 成功</span>
              </div>
              <strong>+2,500</strong>
            </li>
            <li>
              <div>
                <p class="hash">9eaa...1c92</p>
                <span class="meta">TRX · 成功</span>
              </div>
              <strong>-340</strong>
            </li>
            <li>
              <div>
                <p class="hash">6fd1...22c4</p>
                <span class="meta">USDT/TRC20 · 待确认</span>
              </div>
              <strong>+120</strong>
            </li>
          </ul>
          <button class="ghost" type="button">查看全部交易</button>
        </div>
      </section>
    </header>

    <main>
      <section id="features" class="section features">
        <h2>核心功能</h2>
        <div class="grid">
          <article>
            <h3>地址总览</h3>
            <p>聚合 TRX 与 USDT(TRC20) 余额、权限、能量/带宽消耗。</p>
          </article>
          <article>
            <h3>交易追踪</h3>
            <p>按时间、状态筛选交易，并支持 CSV/JSON 导出。</p>
          </article>
          <article>
            <h3>智能提示</h3>
            <p>通过关键字识别地址/合约/交易哈希，减少输入错误。</p>
          </article>
          <article>
            <h3>合规信息</h3>
            <p>提供合约元数据、标签、风险提示与审计链接。</p>
          </article>
        </div>
      </section>

      <section id="tokens" class="section tokens">
        <div class="section-header">
          <h2>资产概览</h2>
          <button class="ghost" type="button">查看更多</button>
        </div>
        <div class="token-cards">
          <article>
            <h3>TRX</h3>
            <p>主网能量与带宽资源统计，实时链上流通。</p>
            <div class="token-meta">
              <span>价格</span>
              <strong>$0.12</strong>
            </div>
          </article>
          <article>
            <h3>USDT (TRC20)</h3>
            <p>稳定币发行与转账数据，供应量与持有人分布。</p>
            <div class="token-meta">
              <span>供应量</span>
              <strong>41.8B</strong>
            </div>
          </article>
          <article>
            <h3>热门合约</h3>
            <p>追踪热门 DApp 与合约交互数据，支持标签检索。</p>
            <div class="token-meta">
              <span>日活</span>
              <strong>268K</strong>
            </div>
          </article>
        </div>
      </section>

      <section id="transactions" class="section transactions">
        <div class="section-header">
          <h2>交易列表</h2>
          <div class="filters">
            <button class="pill active" type="button">全部</button>
            <button class="pill" type="button">USDT/TRC20</button>
            <button class="pill" type="button">TRX</button>
            <button class="pill" type="button">待确认</button>
          </div>
        </div>
        <div class="table">
          <div class="table-row table-head">
            <span>交易哈希</span>
            <span>类型</span>
            <span>区块</span>
            <span>时间</span>
            <span>金额</span>
            <span>状态</span>
          </div>
          <div class="table-row">
            <span class="hash">0x93d...8a2</span>
            <span>USDT/TRC20</span>
            <span>55,219,384</span>
            <span>2 分钟前</span>
            <span>1,250</span>
            <span class="success">成功</span>
          </div>
          <div class="table-row">
            <span class="hash">0xfa1...99d</span>
            <span>TRX</span>
            <span>55,219,221</span>
            <span>8 分钟前</span>
            <span>420</span>
            <span class="success">成功</span>
          </div>
          <div class="table-row">
            <span class="hash">0xa2c...b11</span>
            <span>USDT/TRC20</span>
            <span>55,219,102</span>
            <span>12 分钟前</span>
            <span>3,800</span>
            <span class="pending">确认中</span>
          </div>
        </div>
      </section>

      <section id="faq" class="section faq">
        <h2>常见问题</h2>
        <details>
          <summary>如何查询地址交易记录？</summary>
          <p>在搜索框中输入地址或交易哈希，即可查看明细、余额与最新交易。</p>
        </details>
        <details>
          <summary>支持哪些网络资产？</summary>
          <p>当前支持 TRON 网络的 TRX 与 USDT(TRC20) 资产查询。</p>
        </details>
        <details>
          <summary>是否支持导出报表？</summary>
          <p>在交易列表中选择导出格式即可生成 CSV/JSON 报表。</p>
        </details>
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
