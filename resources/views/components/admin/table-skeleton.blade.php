@props(['rows' => 5, 'columns' => 4, 'checkbox' => true])

<x-admin.table>
    <thead class="bg-slate-50">
        <tr>
            @if($checkbox)
            <th class="px-5 py-3.5 w-10">
                <div class="h-5 w-5 rounded bg-slate-200 animate-pulse"></div>
            </th>
            @endif
            @for($i = 0; $i < $columns; $i++)
                <th class="px-5 py-3.5">
                    <div class="h-3 rounded bg-slate-200 animate-pulse {{ $i % 2 === 0 ? 'w-24' : 'w-20' }}"></div>
                </th>
            @endfor
            <th class="px-5 py-3.5 text-center">
                <div class="h-3 w-16 rounded bg-slate-200 animate-pulse mx-auto"></div>
            </th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        @for($r = 0; $r < $rows; $r++)
            <tr>
                @if($checkbox)
                <td class="px-5 py-4 w-10">
                    <div class="h-5 w-5 rounded bg-slate-100 animate-pulse"></div>
                </td>
                @endif
                @for($c = 0; $c < $columns; $c++)
                    <td class="px-5 py-4">
                        @if($c === 0)
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-slate-200 animate-pulse"></div>
                                <div class="space-y-1.5">
                                    <div class="h-4 rounded bg-slate-200 animate-pulse {{ $r % 3 === 0 ? 'w-32' : ($r % 3 === 1 ? 'w-28' : 'w-36') }}"></div>
                                    <div class="h-3 rounded bg-slate-100 animate-pulse w-20"></div>
                                </div>
                            </div>
                        @elseif($c === $columns - 1)
                            <div class="flex justify-center">
                                <div class="h-6 w-16 rounded-full bg-slate-100 animate-pulse"></div>
                            </div>
                        @else
                            <div class="h-4 rounded bg-slate-100 animate-pulse {{ ($c + $r) % 3 === 0 ? 'w-full' : (($c + $r) % 3 === 1 ? 'w-3/4' : 'w-2/3') }}"></div>
                        @endif
                    </td>
                @endfor
                <td class="px-5 py-4">
                    <div class="flex justify-center gap-1">
                        <div class="h-9 w-9 rounded-xl bg-slate-100 animate-pulse"></div>
                        <div class="h-9 w-9 rounded-xl bg-slate-100 animate-pulse"></div>
                        <div class="h-9 w-9 rounded-xl bg-slate-100 animate-pulse"></div>
                    </div>
                </td>
            </tr>
        @endfor
    </tbody>
</x-admin.table>
