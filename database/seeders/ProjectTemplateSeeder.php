<?php

namespace Database\Seeders;

use App\Models\ProjectTemplate;
use Illuminate\Database\Seeder;

class ProjectTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Real Estate & Construction Template',
                'project_type' => 'construction',
                'description' => 'Comprehensive multi-phase template for commercial & residential building projects.',
                'is_default' => true,
                'structure_json' => [
                    'phases' => [
                        [
                            'name' => '1. Foundation & Groundwork',
                            'tasks' => [
                                [
                                    'title' => 'Site Excavation & Clearing',
                                    'subtasks' => ['Soil Testing & Survey', 'Heavy Machinery Earthwork', 'Debris Removal'],
                                ],
                                [
                                    'title' => 'Footing & Piling Work',
                                    'subtasks' => ['Drilling & Casing', 'Rebar Cage Installation', 'Concrete Pouring'],
                                ],
                                [
                                    'title' => 'Grade Beam & Underground Plumbing',
                                    'subtasks' => ['Trenching for Pipes', 'Grade Beam Formwork', 'Waterproofing Layer'],
                                ],
                            ],
                        ],
                        [
                            'name' => '2. Superstructure & Masonry',
                            'tasks' => [
                                [
                                    'title' => 'Columns & Shear Walls',
                                    'subtasks' => ['Steel Rebar Binding', 'Shuttering Formwork', 'Casting & Curing'],
                                ],
                                [
                                    'title' => 'Slabs & Beams Construction',
                                    'subtasks' => ['Slab Formwork', 'Concealed Conduit Placement', 'Concrete Casting'],
                                ],
                                [
                                    'title' => 'Brickwork & External Facade',
                                    'subtasks' => ['Perimeter Walls', 'Internal Partitions', 'Mortar Joints Inspection'],
                                ],
                            ],
                        ],
                        [
                            'name' => '3. MEP & Internal Finishing',
                            'tasks' => [
                                [
                                    'title' => 'Electrical & Plumbing Conduit',
                                    'subtasks' => ['Wiring & DB Boxes', 'Sanitary Line Rough-in', 'Pressure Leak Test'],
                                ],
                                [
                                    'title' => 'Plaster & Tiles Work',
                                    'subtasks' => ['Wall Plastering', 'Floor Tile Laying', 'Bathroom Waterproofing'],
                                ],
                                [
                                    'title' => 'Doors, Windows & Painting',
                                    'subtasks' => ['Aluminum Frames Fitting', 'Base Primer Coat', 'Final Topcoat Paint'],
                                ],
                            ],
                        ],
                        [
                            'name' => '4. Handover & Commissioning',
                            'tasks' => [
                                [
                                    'title' => 'Deep Cleaning & Quality Punch List',
                                    'subtasks' => ['Site Waste Clearance', 'Snagging Defect Rectification', 'Client Handover Inspection'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Software & Website Development Template',
                'project_type' => 'software',
                'description' => 'Agile/Milestone-based project template for web applications, mobile apps, and SaaS platforms.',
                'is_default' => true,
                'structure_json' => [
                    'phases' => [
                        [
                            'name' => '1. Requirement & Architecture',
                            'tasks' => [
                                [
                                    'title' => 'Stakeholder Requirements Meeting',
                                    'subtasks' => ['User Stories Definition', 'Scope Statement Signoff', 'Tech Stack Selection'],
                                ],
                                [
                                    'title' => 'Database Schema & Architecture Design',
                                    'subtasks' => ['ERD Design', 'API Specification / Swagger', 'System Architecture Diagram'],
                                ],
                            ],
                        ],
                        [
                            'name' => '2. UI/UX Prototyping',
                            'tasks' => [
                                [
                                    'title' => 'Figma Wireframes & User Journey',
                                    'subtasks' => ['Low-fidelity Wireframes', 'Component Design System', 'High-fidelity Mockups'],
                                ],
                                [
                                    'title' => 'Prototype & Client Review',
                                    'subtasks' => ['Interactive Prototype Demo', 'Feedback Iteration', 'Design Approval'],
                                ],
                            ],
                        ],
                        [
                            'name' => '3. Core Development',
                            'tasks' => [
                                [
                                    'title' => 'Backend API Development',
                                    'subtasks' => ['Database Migrations & Models', 'Authentication & JWT/Sanctum', 'Business Logic Services', 'RESTful API Endpoints'],
                                ],
                                [
                                    'title' => 'Frontend Web UI Implementation',
                                    'subtasks' => ['Layout & Navigation Components', 'Responsive Page Views', 'API Integration & State Management', 'Form Validations'],
                                ],
                            ],
                        ],
                        [
                            'name' => '4. Testing, QA & Deployment',
                            'tasks' => [
                                [
                                    'title' => 'Quality Assurance & Bug Fixing',
                                    'subtasks' => ['Unit & Integration Tests', 'Cross-browser & Mobile Testing', 'Security & Performance Audit'],
                                ],
                                [
                                    'title' => 'Production Deployment & Launch',
                                    'subtasks' => ['Server Provisioning & SSL Setup', 'CI/CD Pipeline Configuration', 'Live Migration & Smoke Testing'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Digital Marketing & Growth Agency Template',
                'project_type' => 'marketing',
                'description' => 'End-to-end campaign execution template for performance marketing, SEO, and social growth.',
                'is_default' => true,
                'structure_json' => [
                    'phases' => [
                        [
                            'name' => '1. Audit & Strategy',
                            'tasks' => [
                                [
                                    'title' => 'Competitor Analysis & Market Research',
                                    'subtasks' => ['Audience Persona Definition', 'Keyword Gap Analysis', 'Brand Messaging Document'],
                                ],
                            ],
                        ],
                        [
                            'name' => '2. Creative Asset Production',
                            'tasks' => [
                                [
                                    'title' => 'Ad Creatives & Copywriting',
                                    'subtasks' => ['Video Ad Scripts', 'Banner Visuals & Carousels', 'Landing Page Copy'],
                                ],
                            ],
                        ],
                        [
                            'name' => '3. Campaign Launch & Scaling',
                            'tasks' => [
                                [
                                    'title' => 'Meta & Google Ads Setup',
                                    'subtasks' => ['Pixel & Conversion API Tracking', 'Audience Segment Targeting', 'A/B Test Ad Sets'],
                                ],
                                [
                                    'title' => 'Weekly Optimization & ROI Reporting',
                                    'subtasks' => ['Bid & Budget Optimization', 'CPA & ROAS Analysis', 'Client Performance Review'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($templates as $t) {
            ProjectTemplate::updateOrCreate(
                ['name' => $t['name']],
                $t
            );
        }
    }
}
