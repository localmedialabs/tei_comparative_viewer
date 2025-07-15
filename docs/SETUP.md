## 💻 システム要件

このアプリケーションを正しく動作させるには、以下のソフトウェアとバージョンが必要です。

| ソフトウェア | バージョン          | 備考                     |
| ------------ | ------------------- | ------------------------ |
| PHP          | 8.2 以上            | Laravel 11 を使用        |
| Composer     | 2.x （latest 推奨） | PHP 依存パッケージ管理   |
| Node.js      | 22.x                | フロントエンド開発に使用 |
| npm          | 10.x                | Node.js に付属           |

### ✅ 動作確認済の環境例

- PHP 8.2.25
- Composer 2.8.2
- Node.js 22.14.0
- npm 10.9.2

## ⚙️ セットアップ手順

### 1. リポジトリをクローン

```bash
git clone https://github.com/localmedialabs/tei_viewer.git
cd tei_viewer
```

### 2. Laravel バックエンドのセットアップ

```bash
cp .env.example .env
composer install
php artisan key:generate
```

### 3. フロントエンドのセットアップ

```bash
npm install
```

### 4. 開発サーバーの起動

以下の 2 つのコマンドを**別のターミナルで同時に実行してください**：

・Laravel アプリケーション（バックエンド API）：

```bash
php artisan serve
```

・Vite 開発サーバー（フロントエンド）：

```bash
npm run dev
```
