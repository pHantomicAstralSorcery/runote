<th class="text-center">
                                <a href="{{ route($route, array_merge($custom_req ?? request()->query(), ['sort' => $column, 'order' => request('order') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none">
                                    {{ $label }}
                                    @if(request('sort') === $column)
                                        {{ request('order') === 'asc' ? '↑' : '↓' }}
                                    @endif
                                </a>
                            </th>