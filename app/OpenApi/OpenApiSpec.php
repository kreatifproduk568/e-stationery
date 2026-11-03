<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'E-Stationery API',
    version: '1.0.0',
    description: 'Dokumentasi REST API aplikasi E-Stationery'
)]
#[OA\Server(
    url: 'http://127.0.0.1:8000',
    description: 'Laravel Development Server'
)]
#[OA\Get(
    path: '/api/health',
    operationId: 'getApiHealth',
    summary: 'Memeriksa status API',
    tags: ['System'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'API berjalan dengan baik',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'E-Stationery API berjalan'
                    )
                ],
                type: 'object'
            )
        )
    ]
)]



#[OA\Get(
    path: '/api/products',
    operationId: 'getProducts',
    summary: 'Mengambil daftar produk',
    description: 'Mengambil maksimal 10 produk beserta relasi kategori dan merek.',
    tags: ['Products'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Data produk berhasil diambil',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'success',
                        type: 'boolean',
                        example: true
                    ),
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Data produk berhasil diambil'
                    ),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'id',
                                    type: 'string',
                                    format: 'uuid',
                                    example: '0199a111-2222-7333-8444-555566667777'
                                ),
                                new OA\Property(
                                    property: 'name',
                                    type: 'string',
                                    example: 'Buku Tulis'
                                ),
                                new OA\Property(
                                    property: 'sku',
                                    type: 'string',
                                    example: 'ATK-001'
                                )
                            ]
                        )
                    ),
                    new OA\Property(
                        property: 'links',
                        type: 'object'
                    ),
                    new OA\Property(
                        property: 'meta',
                        type: 'object'
                    )
                ],
                type: 'object'
            )
        )
    ]
)]


class OpenApiSpec
{
}
