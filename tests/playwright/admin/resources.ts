import { execFileSync } from 'node:child_process';

export type FieldType =
  | 'checkbox'
  | 'date'
  | 'email'
  | 'file'
  | 'number'
  | 'password'
  | 'photos'
  | 'relation_files'
  | 'section'
  | 'select'
  | 'tel'
  | 'text'
  | 'textarea';

export interface AdminField {
  name: string;
  label: string;
  type: FieldType;
  options: Record<string, string>;
  required?: boolean;
  readonly?: boolean;
  hide_on_create?: boolean;
  accept?: string;
}

export interface ResourceDef {
  slug: string;
  label: string;
  group: string;
  columns: string[];
  filters: Record<string, Record<string, string>>;
  fields: AdminField[];
}

export interface AdminGroupDef {
  key: string;
  label: string;
  resources: ResourceDef[];
}

const wantedSlugs = new Set([
  'pengaduan-pengendalian',
  'permohonan-rekomendasi',
  'pengajuan-rintek-pertek',
  'registrasi-usaha-lb3',
  'jenis-lb3',
  'titik-tpa',
  'titik-tpst',
  'titik-tps',
  'bank-sampah',
  'jadwal-armada',
  'statistik-sampah',
  'perizinan-tebang-pohon',
  'pinjam-taman',
  'data-tanam-pohon',
  'pengaduan-tata-penataan',
  'objek-pengawasan',
  'sidak',
  'pelanggaran',
  'sanksi',
  'sosialisasi',
  'artikel',
  'ikm-response',
  'email-notification-log',
  'user',
]);

function loadFromAdminRegistry(): AdminGroupDef[] {
  const script = `
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
$groups = App\\Support\\Admin\\AdminRegistry::all();
$out = [];
foreach ($groups as $groupKey => $group) {
    $resources = [];
    foreach ($group['items'] as $item) {
        $item['group'] = $groupKey;
        $item['fields'] = App\\Support\\Admin\\AdminRegistry::formFields($item);
        $item['model'] = class_basename($item['model']);
        $resources[] = $item;
    }
    $out[] = ['key' => $groupKey, 'label' => $group['label'], 'resources' => $resources];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
`;

  const output = execFileSync('php', ['-r', script], {
    cwd: process.cwd(),
    encoding: 'utf8',
    env: { ...process.env, APP_ENV: process.env.APP_ENV ?? 'testing' },
  });

  return JSON.parse(output) as AdminGroupDef[];
}

export const groupedResources = loadFromAdminRegistry()
  .map((group) => ({
    ...group,
    resources: group.resources.filter((resource) => wantedSlugs.has(resource.slug)),
  }))
  .filter((group) => group.resources.length > 0);

export const allResources = groupedResources.flatMap((group) => group.resources);

export function firstEditableField(resource: ResourceDef): AdminField | undefined {
  // Prefer text-like fields over numeric for edit tests
  const textFields = resource.fields.find((field) => {
    if (field.name.startsWith('_section_')) return false;
    if (field.readonly || field.hide_on_create) return false;
    return ['email', 'tel', 'text', 'textarea'].includes(field.type);
  });
  if (textFields) return textFields;

  // Fallback to number fields
  return resource.fields.find((field) => {
    if (field.name.startsWith('_section_')) return false;
    if (field.readonly || field.hide_on_create) return false;
    return field.type === 'number';
  });
}

