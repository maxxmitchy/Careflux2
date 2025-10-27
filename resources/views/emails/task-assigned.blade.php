<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Task Assigned</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f2f4f6; color: #1a202c; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .header { background-color: #0d9488; padding: 24px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 24px; font-weight: bold; }
        .content { padding: 32px; }
        .content h2 { margin-top: 0; color: #2d3748; font-size: 20px; font-weight: bold; }
        .content p { margin: 4px 0; font-size: 16px; line-height: 1.6; color: #4a5568; }
        .task-details { margin: 24px 0; padding: 16px; background-color: #f7fafc; border-left: 4px solid #0d9488; }
        .button-container { text-align: center; margin-top: 32px; }
        .button { display: inline-block; padding: 12px 24px; background-color: #0d9488; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; }
        .footer { padding: 24px; text-align: center; font-size: 12px; color: #718096; }
    </style>
</head>
<body>
    <table role="presentation" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <div class="container">
                    <!-- Header -->
                    <div class="header">
                        <h1>New Task Assigned</h1>
                    </div>

                    <!-- Content -->
                    <div class="content">
                        <h2>Hello {{ $task->assignee->name }},</h2>
                        <p>A new task has been assigned to you on the Careflux platform.</p>

                        <div class="task-details">
                            <p><strong>Task:</strong><br>{{ $task->taskDefinition->name }}</p>
                            @if($task->due_at)
                                <p style="margin-top: 12px;"><strong>Due Date:</strong><br>{{ $task->due_at->format('F d, Y') }}</p>
                            @endif
                            @if($task->creator)
                                <p style="margin-top: 12px;"><strong>Assigned By:</strong><br>{{ $task->creator->name }}</p>
                            @endif
                        </div>

                        <p>Please log in to your dashboard to view the full details and complete the task.</p>

                        <div class="button-container">
                            @php
                                // Determine the correct panel URL based on the user's role
                                $panelId = $task->assignee->is_pharmacist ? 'pharmacy' : 'technician';
                                $url = route("filament.{$panelId}.resources.task-resource.index");
                            @endphp
                            <a href="{{ $url }}" class="button">View My Tasks</a>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <p>&copy; {{ date('Y') }} Careflux. All rights reserved.</p>
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
