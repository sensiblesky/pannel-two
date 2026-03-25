<?php

namespace App\Main;

class AgentSidebarPanel
{
    public static function tags(): array
    {
        return [
            'title' => 'Tags',
            'items' => [
                [
                    'agent_tickets_tags' => [
                        'title' => 'All Tags',
                        'route_name' => 'agent.tickets/settings-tags',
                    ],
                ],
            ],
        ];
    }

    public static function cannedResponses(): array
    {
        return [
            'title' => 'Canned Responses',
            'items' => [
                [
                    'agent_tickets_canned_responses' => [
                        'title' => 'All Canned Responses',
                        'route_name' => 'agent.tickets/settings-canned-responses',
                    ],
                ],
            ],
        ];
    }

    public static function tickets(): array
    {
        return [
            'title' => 'Tickets',
            'items' => [
                [
                    'agent_tickets_dashboard' => [
                        'title' => 'Dashboard',
                        'route_name' => 'agent.tickets/dashboard',
                    ],
                ],
                [
                    'agent_tickets_all' => [
                        'title' => 'All Tickets',
                        'route_name' => 'agent.tickets/index',
                    ],
                    'agent_tickets_create' => [
                        'title' => 'Create Ticket',
                        'route_name' => 'agent.tickets/create',
                    ],
                ],
                [
                    'agent_tickets_reports' => [
                        'title' => 'Reports',
                        'route_name' => 'agent.tickets/reports',
                    ],
                ],
            ],
            
        ];
    }
}
