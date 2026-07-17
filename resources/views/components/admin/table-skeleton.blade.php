@props(['rows' => 5, 'columns' => 4])

<x-admin.table>
    <thead class="bg-slate-50">
        <tr>
            @for($i = 0; $i < $columns; $i++)
                <th class="px-5 py-4">
                    <x-admin.skeleton width="1/2" height="sm" />
                </th>
            @endfor
            <th class="px-5 py-4 text-right">
                <x-admin.skeleton width="1/3" height="sm" class="ml-auto" />
            </th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @for($r = 0; $r < $rows; $r++)
            <tr>
                @for($c = 0; $c < $columns; $c++)
                    <td class="px-5 py-4">
                        <x-admin.skeleton :width="$c % 2 === 0 ? '3/4' : '1/2'" />
                    </td>
                @endfor
                <td class="px-5 py-4">
                    <div class="flex justify-end gap-2">
                        <x-admin.skeleton width="1/4" height="button" class="w-20" />
                        <x-admin.skeleton width="1/4" height="button" class="w-16" />
                    </div>
                </td>
            </tr>
        @endfor
    </tbody>
</x-admin.table>
