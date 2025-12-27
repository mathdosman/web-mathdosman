<?php
/**
 * Seed paket soal + butir soal (Pilihan Ganda) dari file HTML di folder /soal.
 *
 * Script ini membaca struktur HTML seperti:
 * - <ol type="1"> ... <li>PERTANYAAN</li> <ol type="A">...opsi A-E...</ol> ... JAWABAN : B ...
 * lalu menyimpannya ke tabel:
 * - packages
 * - questions
 * - package_questions
 *
 * Usage (Windows/XAMPP):
 *   C:\xampp\php\php.exe scripts\seed_packages_questions_from_soal_html.php
 *
 * Options:
 *   --folder=soal              Folder sumber (default: ../soal)
 *   --dry-run=1                Tidak menulis DB
 *   --limit=5                  Batasi jumlah file
 *   --skip-existing=1          Skip jika code paket sudah ada (default: 1)
 *   --overwrite=1              Jika paket sudah ada (code sama), hapus relasi+soal lama lalu import ulang
 *   --package-status=draft     draft|published (default: draft)
 *   --question-status=draft    draft|published (default: draft)
 *   --show-answers-public=0    0|1 (default: 0)
 *   --subject=Matematika       Nama mapel (default: Matematika)
 *   --materi=US                Override materi paket+soal (opsional)
 *   --submateri=Wajib Kelas12  Override submateri paket+soal (opsional)
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
	fwrite(STDERR, "This script must be run via CLI.\n");
	exit(1);
}

require_once __DIR__ . '/../config/db.php';

/** @var PDO $pdo */

function argValue(array $argv, string $name, ?string $default = null): ?string
{
	$prefix = '--' . $name . '=';
	foreach ($argv as $a) {
		if (str_starts_with($a, $prefix)) {
			return substr($a, strlen($prefix));
		}
	}
	return $default;
}

function argBool(array $argv, string $name, bool $default = false): bool
{
	$v = argValue($argv, $name, null);
	if ($v === null) {
		return $default;
	}
	return in_array(strtolower(trim($v)), ['1', 'true', 'yes', 'y', 'on'], true);
}

function slugify(string $s): string
{
	$s = trim($s);
	$s = strtolower($s);
	$s = preg_replace('/[^a-z0-9\-_\s]+/', '', $s);
	$s = preg_replace('/\s+/', '-', $s);
	$s = preg_replace('/-+/', '-', $s);
	$s = trim($s, '-');
	return $s !== '' ? $s : 'paket';
}

function loadHtmlDocument(string $html): DOMDocument
{
	// Some source files contain stray characters like "</head>1" or "</ul>5".
	$html = preg_replace('/<\/head>\s*\d+\s*<body/i', '</head><body', $html);
	$html = preg_replace('/<\/ul>\s*\d+\s*<\/div>/i', '</ul></div>', $html);

	$prev = libxml_use_internal_errors(true);
	$doc = new DOMDocument();
	$doc->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
	libxml_clear_errors();
	libxml_use_internal_errors($prev);
	return $doc;
}

function innerHtml(DOMNode $node): string
{
	$html = '';
	foreach ($node->childNodes as $child) {
		$html .= $node->ownerDocument->saveHTML($child);
	}
	return trim($html);
}

function outerHtml(DOMNode $node): string
{
	return trim($node->ownerDocument->saveHTML($node));
}

function basicAllowlistHtml(string $html): string
{
	$html = trim($html);
	if ($html === '') {
		return '';
	}

	$wrap = '<div>' . $html . '</div>';

	$prev = libxml_use_internal_errors(true);
	$doc = new DOMDocument();
	$doc->loadHTML('<?xml encoding="utf-8" ?>' . $wrap, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
	libxml_clear_errors();
	libxml_use_internal_errors($prev);

	$root = $doc->getElementsByTagName('div')->item(0);
	if (!$root) {
		return '';
	}

	$allowedTags = [
		'div' => ['class'],
		'p' => ['class'],
		'h1' => ['class'],
		'h2' => ['class'],
		'h3' => ['class'],
		'h4' => ['class'],
		'h5' => ['class'],
		'h6' => ['class'],
		'br' => [],
		'hr' => [],
		'b' => ['class'],
		'strong' => ['class'],
		'i' => ['class'],
		'em' => ['class'],
		'u' => ['class'],
		'ul' => ['class'],
		'ol' => ['class', 'type'],
		'li' => ['class'],
		'blockquote' => ['class'],
		'code' => ['class'],
		'pre' => ['class'],
		'sub' => [],
		'sup' => [],
		'span' => ['class'],
		'img' => ['src', 'alt', 'width', 'height', 'class'],
		'table' => ['class'],
		'thead' => ['class'],
		'tbody' => ['class'],
		'tr' => ['class'],
		'th' => ['class', 'colspan', 'rowspan'],
		'td' => ['class', 'colspan', 'rowspan'],
		'a' => ['href', 'title', 'target', 'rel', 'class'],
	];

	$isSafeUrl = static function (?string $url): bool {
		if (!$url) {
			return false;
		}
		$url = trim($url);
		if ($url === '') {
			return false;
		}
		$lower = strtolower($url);
		if (str_starts_with($lower, 'javascript:') || str_starts_with($lower, 'data:')) {
			return false;
		}
		if (str_starts_with($url, '#')) {
			return true;
		}
		if (!str_contains($url, '://')) {
			return true; // relative
		}
		$scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
		return in_array($scheme, ['http', 'https'], true);
	};

	$cleanNode = static function (DOMNode $node) use (&$cleanNode, $doc, $allowedTags, $isSafeUrl): void {
		if (!($node instanceof DOMElement)) {
			return;
		}

		$tag = strtolower($node->tagName);
		if (in_array($tag, ['script', 'style'], true)) {
			$node->parentNode?->removeChild($node);
			return;
		}

		if (!array_key_exists($tag, $allowedTags)) {
			$parent = $node->parentNode;
			if ($parent) {
				while ($node->firstChild) {
					$parent->insertBefore($node->firstChild, $node);
				}
				$parent->removeChild($node);
			}
			return;
		}

		$allowedAttrs = $allowedTags[$tag];
		if ($node->hasAttributes()) {
			$toRemove = [];
			foreach ($node->attributes as $attr) {
				$name = strtolower($attr->name);
				if (str_starts_with($name, 'on')) {
					$toRemove[] = $attr->name;
					continue;
				}
				if (!in_array($name, $allowedAttrs, true)) {
					$toRemove[] = $attr->name;
				}
			}
			foreach ($toRemove as $a) {
				$node->removeAttribute($a);
			}
		}

		if ($tag === 'a') {
			$href = $node->getAttribute('href');
			if ($href !== '' && !$isSafeUrl($href)) {
				$node->removeAttribute('href');
			}
		}
		if ($tag === 'img') {
			$src = $node->getAttribute('src');
			if ($src !== '' && !$isSafeUrl($src)) {
				$node->parentNode?->removeChild($node);
				return;
			}
		}

		$children = [];
		foreach ($node->childNodes as $c) {
			$children[] = $c;
		}
		foreach ($children as $c) {
			$cleanNode($c);
		}
	};

	$cleanNode($root);
	return innerHtml($root);
}

function letterToJawabanField(string $letter): ?string
{
	$letter = strtoupper(trim($letter));
	return match ($letter) {
		'A' => 'pilihan_1',
		'B' => 'pilihan_2',
		'C' => 'pilihan_3',
		'D' => 'pilihan_4',
		'E' => 'pilihan_5',
		default => null,
	};
}

function ensureSubjectId(PDO $pdo, string $name): int
{
	$name = trim($name);
	$stmt = $pdo->prepare('SELECT id FROM subjects WHERE name = :n LIMIT 1');
	$stmt->execute([':n' => $name]);
	$id = (int)($stmt->fetchColumn() ?: 0);
	if ($id > 0) {
		return $id;
	}

	$stmt = $pdo->prepare('INSERT INTO subjects (name, description) VALUES (:n, NULL)');
	$stmt->execute([':n' => $name]);
	return (int)$pdo->lastInsertId();
}

function ensureUniquePackageCode(PDO $pdo, string $baseCode): string
{
	$code = $baseCode;
	$i = 2;
	while (true) {
		$stmt = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
		$stmt->execute([':c' => $code]);
		$exists = (int)($stmt->fetchColumn() ?: 0);
		if ($exists <= 0) {
			return $code;
		}
		$code = $baseCode . '-' . $i;
		$i++;
		if ($i > 999) {
			throw new RuntimeException('Could not find unique package code for: ' . $baseCode);
		}
	}
}

function detectMateriFromFilename(string $baseNoExt): ?string
{
	$u = strtoupper($baseNoExt);
	if (str_contains($u, 'PAT')) {
		return 'PAT';
	}
	if (str_contains($u, 'PAS')) {
		return 'PAS';
	}
	if (str_contains($u, 'US')) {
		return 'US';
	}
	return null;
}

function detectSubmateriFromFilename(string $baseNoExt): ?string
{
	$l = strtolower($baseNoExt);
	$parts = [];

	if (str_contains($l, 'wajib')) {
		$parts[] = 'Wajib';
	} elseif (str_contains($l, 'minat')) {
		$parts[] = 'Minat';
	}

	if (preg_match('/kelas\s*([0-9]{1,2})/i', $baseNoExt, $m)) {
		$parts[] = 'Kelas' . $m[1];
	} else {
		if (preg_match('/\b10\b/', $baseNoExt)) {
			$parts[] = 'Kelas10';
		} elseif (preg_match('/\b11\b/', $baseNoExt)) {
			$parts[] = 'Kelas11';
		} elseif (preg_match('/\b12\b/', $baseNoExt)) {
			$parts[] = 'Kelas12';
		}
	}

	$s = trim(implode(' ', $parts));
	return $s !== '' ? $s : null;
}

$folderArg = argValue($argv, 'folder', null);
$folder = $folderArg ? $folderArg : (__DIR__ . '/../soal');
if (!str_contains($folder, ':') && !str_starts_with($folder, '/') && !str_starts_with($folder, '\\')) {
	$folder = realpath(__DIR__ . '/../' . trim($folder, '/\\')) ?: $folder;
}

$dryRun = argBool($argv, 'dry-run', false);
$skipExisting = argBool($argv, 'skip-existing', true);
$overwrite = argBool($argv, 'overwrite', false);
$limit = (int)(argValue($argv, 'limit', '0') ?: 0);
$packageStatus = (string)(argValue($argv, 'package-status', 'draft') ?: 'draft');
$questionStatus = (string)(argValue($argv, 'question-status', 'draft') ?: 'draft');
$showAnswersPublic = argBool($argv, 'show-answers-public', false) ? 1 : 0;
$subjectName = (string)(argValue($argv, 'subject', 'Matematika') ?: 'Matematika');
$materiOverride = argValue($argv, 'materi', null);
$submateriOverride = argValue($argv, 'submateri', null);

if (!is_dir($folder)) {
	fwrite(STDERR, "Folder not found: {$folder}\n");
	exit(1);
}

$files = glob(rtrim($folder, '/\\') . '/*.html');
sort($files);
if ($limit > 0) {
	$files = array_slice($files, 0, $limit);
}

if (!$files) {
	fwrite(STDERR, "No .html files found in: {$folder}\n");
	exit(1);
}

$subjectId = ensureSubjectId($pdo, $subjectName);

fwrite(STDOUT, "Folder: {$folder}\n");
fwrite(STDOUT, "Files: " . count($files) . "\n");
fwrite(STDOUT, "Subject: {$subjectName} (id={$subjectId})\n");
fwrite(STDOUT, "Dry-run: " . ($dryRun ? 'YES' : 'NO') . "\n\n");

$createdPackages = 0;
$createdQuestions = 0;

foreach ($files as $filePath) {
	$base = basename($filePath);
	$baseNoExt = preg_replace('/\.html$/i', '', $base);

	$html = file_get_contents($filePath);
	if ($html === false || trim($html) === '') {
		fwrite(STDOUT, "[SKIP] {$base} (empty)\n");
		continue;
	}

	$doc = loadHtmlDocument($html);
	$xpath = new DOMXPath($doc);

	$h2 = $xpath->query('//h2')->item(0);
	$h3 = $xpath->query('//h3')->item(0);
	$titleNode = $xpath->query('//title')->item(0);

	$titleParts = [];
	$t1 = $h2 ? trim($h2->textContent) : '';
	$t2 = $h3 ? trim($h3->textContent) : '';
	$t3 = $titleNode ? trim($titleNode->textContent) : '';

	if ($t1 !== '') {
		$titleParts[] = $t1;
	}
	if ($t2 !== '') {
		$titleParts[] = $t2;
	}
	if (!$titleParts && $t3 !== '') {
		$titleParts[] = $t3;
	}
	if (!$titleParts) {
		$titleParts[] = $baseNoExt;
	}

	$packageName = trim(implode(' - ', $titleParts));
	$packageCodeBase = slugify($baseNoExt);

	$materi = $materiOverride !== null ? trim($materiOverride) : (detectMateriFromFilename($baseNoExt) ?? null);
	$submateri = $submateriOverride !== null ? trim($submateriOverride) : (detectSubmateriFromFilename($baseNoExt) ?? null);
	if ($materi === '') {
		$materi = null;
	}
	if ($submateri === '') {
		$submateri = null;
	}
	if ($materi !== null && strlen($materi) > 150) {
		$materi = substr($materi, 0, 150);
	}
	if ($submateri !== null && strlen($submateri) > 150) {
		$submateri = substr($submateri, 0, 150);
	}

	$stmt = $pdo->prepare('SELECT id FROM packages WHERE code = :c LIMIT 1');
	$stmt->execute([':c' => $packageCodeBase]);
	$existingPackageId = (int)($stmt->fetchColumn() ?: 0);
	if ($existingPackageId > 0 && $skipExisting && !$overwrite) {
		fwrite(STDOUT, "[SKIP] {$base} (package exists: {$packageCodeBase})\n");
		continue;
	}

	$ol = $xpath->query("//ol[@type='1']")->item(0);
	if (!$ol) {
		fwrite(STDOUT, "[SKIP] {$base} (no <ol type=\"1\"> found)\n");
		continue;
	}

	$questionLis = (new DOMXPath($doc))->query('./li', $ol);
	if (!$questionLis || $questionLis->length <= 0) {
		fwrite(STDOUT, "[SKIP] {$base} (no question <li> direct children)\n");
		continue;
	}

	try {
		if (!$dryRun) {
			$pdo->beginTransaction();
		}

		$packageCode = $packageCodeBase;
		$packageId = 0;

		if ($existingPackageId > 0 && $overwrite) {
			// Reuse existing package, clear old attachments, and delete questions that are only used by this package.
			$packageId = $existingPackageId;

			if (!$dryRun) {
				$stmtUpd = $pdo->prepare('UPDATE packages SET name = :n, subject_id = :sid, description = :d, show_answers_public = :sap, status = :st, materi = :m, submateri = :sm WHERE id = :id');
				$stmtUpd->execute([
					':id' => $packageId,
					':n' => $packageName,
					':sid' => $subjectId,
					':d' => 'Imported from soal/' . $base,
					':sap' => $showAnswersPublic,
					':st' => $packageStatus,
					':m' => $materi,
					':sm' => $submateri,
				]);

				$stmtQids = $pdo->prepare('SELECT question_id FROM package_questions WHERE package_id = :pid');
				$stmtQids->execute([':pid' => $packageId]);
				$qids = $stmtQids->fetchAll(PDO::FETCH_COLUMN);

				$pdo->prepare('DELETE FROM package_questions WHERE package_id = :pid')->execute([':pid' => $packageId]);

				if ($qids) {
					$stmtCount = $pdo->prepare('SELECT COUNT(*) FROM package_questions WHERE question_id = :qid');
					$stmtDelQ = $pdo->prepare('DELETE FROM questions WHERE id = :qid');
					foreach ($qids as $qid) {
						$qid = (int)$qid;
						if ($qid <= 0) {
							continue;
						}
						$stmtCount->execute([':qid' => $qid]);
						$c = (int)$stmtCount->fetchColumn();
						if ($c <= 0) {
							$stmtDelQ->execute([':qid' => $qid]);
						}
					}
				}
			}
		} else {
			// Create new package (or a unique code variant if base already exists).
			if (!$dryRun) {
				if ($existingPackageId > 0) {
					$packageCode = ensureUniquePackageCode($pdo, $packageCodeBase);
				}

				$stmtPkg = $pdo->prepare('INSERT INTO packages (code, name, subject_id, materi, submateri, intro_content_id, description, show_answers_public, status, published_at) VALUES (:c, :n, :sid, :m, :sm, NULL, :d, :sap, :st, NULL)');
				$stmtPkg->execute([
					':c' => $packageCode,
					':n' => $packageName,
					':sid' => $subjectId,
					':m' => $materi,
					':sm' => $submateri,
					':d' => 'Imported from soal/' . $base,
					':sap' => $showAnswersPublic,
					':st' => $packageStatus,
				]);
				$packageId = (int)$pdo->lastInsertId();
			}
		}

		$qCount = 0;

		for ($idx = 0; $idx < $questionLis->length; $idx++) {
			$qLi = $questionLis->item($idx);
			if (!$qLi instanceof DOMElement) {
				continue;
			}

			$qNumber = $idx + 1;
			$questionHtml = innerHtml($qLi);

			$optionsOl = null;
			$collapseDiv = null;
			$extraHtml = '';

			$node = $qLi->nextSibling;
			while ($node) {
				if ($node instanceof DOMElement && $node->tagName === 'li' && $node->parentNode === $ol) {
					break;
				}

				if ($node instanceof DOMElement) {
					$tag = strtolower($node->tagName);
					if ($tag === 'img') {
						$extraHtml .= outerHtml($node);
					} elseif ($tag === 'ol' && $optionsOl === null) {
						$type = strtoupper(trim((string)$node->getAttribute('type')));
						if ($type === 'A') {
							$optionsOl = $node;
						}
					} elseif ($tag === 'div' && $collapseDiv === null) {
						$cls = ' ' . strtolower((string)$node->getAttribute('class')) . ' ';
						if (str_contains($cls, ' collapse ')) {
							$collapseDiv = $node;
						}
					}
				}

				$node = $node->nextSibling;
			}

			if (trim($extraHtml) !== '') {
				$questionHtml .= '<br>' . $extraHtml;
			}

			$choices = ['', '', '', '', ''];
			if ($optionsOl instanceof DOMElement) {
				$optLis = (new DOMXPath($doc))->query('./li', $optionsOl);
				if ($optLis) {
					for ($j = 0; $j < min(5, $optLis->length); $j++) {
						$choices[$j] = innerHtml($optLis->item($j));
					}
				}
			}

			$jawabanField = null;
			$solutionHtml = '';
			if ($collapseDiv instanceof DOMElement) {
				$cardBody = (new DOMXPath($doc))->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' card-body ')]", $collapseDiv)->item(0);
				$solutionHtml = $cardBody instanceof DOMElement ? innerHtml($cardBody) : innerHtml($collapseDiv);

				$text = strtoupper($collapseDiv->textContent);
				if (preg_match('/JAWABAN\s*:\s*([A-E])/', $text, $m)) {
					$jawabanField = letterToJawabanField($m[1]);
				}

				$solutionHtml = preg_replace('/<h[1-6][^>]*>\s*JAWABAN\s*:\s*[A-E]\s*<\/h[1-6]>\s*/i', '', $solutionHtml);
				$solutionHtml = preg_replace('/<b[^>]*>\s*<h[1-6][^>]*>\s*JAWABAN\s*:\s*[A-E]\s*<\/h[1-6]>\s*<\/b>\s*/i', '', $solutionHtml);
			}

			$questionHtmlDb = basicAllowlistHtml($questionHtml);
			$solutionHtmlDb = basicAllowlistHtml($solutionHtml);
			$c1 = basicAllowlistHtml($choices[0]);
			$c2 = basicAllowlistHtml($choices[1]);
			$c3 = basicAllowlistHtml($choices[2]);
			$c4 = basicAllowlistHtml($choices[3]);
			$c5 = basicAllowlistHtml($choices[4]);

			if (!$dryRun) {
				$stmtQ = $pdo->prepare('INSERT INTO questions (subject_id, pertanyaan, penyelesaian, tipe_soal, pilihan_1, pilihan_2, pilihan_3, pilihan_4, pilihan_5, jawaban_benar, materi, submateri, status_soal) VALUES (:sid, :qt, :pz, :t, :a, :b, :c, :d, :e, :jb, :m, :sm, :st)');
				$stmtQ->execute([
					':sid' => $subjectId,
					':qt' => $questionHtmlDb,
					':pz' => ($solutionHtmlDb === '' ? null : $solutionHtmlDb),
					':t' => 'Pilihan Ganda',
					':a' => $c1,
					':b' => $c2,
					':c' => $c3,
					':d' => $c4,
					':e' => $c5,
					':jb' => ($jawabanField === null ? null : $jawabanField),
					':m' => $materi,
					':sm' => $submateri,
					':st' => $questionStatus,
				]);
				$questionId = (int)$pdo->lastInsertId();

				$stmtAttach = $pdo->prepare('INSERT INTO package_questions (package_id, question_id, question_number) VALUES (:pid, :qid, :no)');
				$stmtAttach->execute([
					':pid' => $packageId,
					':qid' => $questionId,
					':no' => $qNumber,
				]);
			}

			$qCount++;
			$createdQuestions++;
		}

		if (!$dryRun) {
			$pdo->commit();
		}

		$createdPackages++;
		fwrite(STDOUT, "[OK] {$base} => paket={$packageCode}, soal={$qCount}\n");
	} catch (Throwable $e) {
		if (!$dryRun && $pdo->inTransaction()) {
			$pdo->rollBack();
		}
		fwrite(STDOUT, "[ERR] {$base}: {$e->getMessage()}\n");
	}
}

fwrite(STDOUT, "\nDone. Packages created: {$createdPackages}, Questions created: {$createdQuestions}\n");