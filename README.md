# Extended Page Module for Moodle

## Instalation

This extension is designed to enhance the existing Page module in Moodle.
Follow these steps to install the extension:

This plugin should replace the existing page plugin in your Moodle installation directory.

Download it from:

    <https://github.com/NikolayP12/page.git>

Once you have downloaded it, log in to your Moodle site as an admin, navigate to \_Site administration > \_Plugins > \_Plugins overview, look for the Page plugin, and uninstall it. For the uninstall process just follow the steps given by Moodle and the result will be correct. It is necessary to uninstall the original Moodle plugin because the plugin table will change, and to avoid future problems it is better to remove the previous plugin and then install the extended version plugin.

Once uninstalled, all you have to do is go to the following directory and paste the folder you downloaded/cloned from github:

    {your/moodle/dirroot}/mod/

Afterwards, log in to your Moodle site as an admin and go to \_Site administration >
Notifications\_ to complete the installation and follow the steps that Moodle provides for the correct installation.

## License

This extension of the Moodle Page module is distributed under the GNU General Public License (GPLv3 or later), aligning with Moodle's licensing to ensure that it remains free and open source, and can be freely used, modified, and distributed under the same terms as Moodle itself.

The GPLv3 license grants you the following freedoms:

- Freedom to use the software for any purpose.
- Freedom to change the software to suit your needs.
- Freedom to share the software with your friends and neighbors.
- Freedom to share the changes you make.

You should have received a copy of the GNU General Public License along with this program.
If not, see <https://www.gnu.org/licenses/>.

This extension was developed by Nikolay in 2024. For any inquiries, please contact nikolaypn2002@gmail.com.

Original copyright 2009 Petr Skoda (http://skodak.org/)
license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

## Purpose

The purpose of this extension is to bring teachers closer to students and provide better guidance during the study of course concepts. Firstly, a system has been developed for teachers to mark a learning path through the course modules, recommending students follow this path to understand the concepts presented on the page. Additionally, a new text editor has been developed where teachers can comment on concepts directly related to the page's syllabus, helping students better orient themselves and improve their learning curve.

The interface for viewing page sections has been enhanced and modernized to better display content to students. Reading has become lighter, and it's now more intuitive to understand the different sections presented to the student. To improve the functionality of the module, it is recommended to enable Moodle's auto-linking in \_Site administration > \_Plugins > \_Manage filters for the aspects that interest you. To facilitate reading and isolate the intrusion of auto-linking, its application has been disabled in the content section of the page, but it is maintained for the learning path field and the related concepts field.

Finally, a mail submission form for students has been created. This form can be expanded or collapsed to not occupy unnecessary space on the page. The form's goal is to provide students with a more personal and direct way to contact their teacher to resolve any doubts that may arise while viewing and studying the page's content. The form only allows emails to be sent to teachers registered in the course, showing the names and emails of the course's teachers above the form, so students can choose who to send it to and know which emails are available. The approach followed for sending is as follows: the recipient is specified by the student (the teacher's email), while the student's email is placed in the CC of the sent email (this email is retrieved from the user who is logged in at the time of sending the email). The user sending the email is the one configured in the \_Site administration > \_Server > \_Outgoing mail configuration SMTP section. This way, emails will always be sent from the same email, and students will not be asked for authentication. Thus, students and teachers can subsequently continue the conversation thread by replying to the sent email, maintaining the practicality of email sending, the privacy it grants, and encouraging students to send their questions. This will prevent students from postponing sending the email due to having to access their email sending platform, leaving them with doubts.

## Configuration and Usage

Once the plugin is installed, it is ideal to configure the mail sending section correctly before starting to create a page. Go to \_Site administration > \_Server > \_Outgoing mail configuration and configure the fields for SMTP. An example of using an Outlook email is as follows:

- **SMTP hosts**: smtp.office365.com:587 (Specify the port; if not specified, the plugin will default to port 587 by default)

- **SMTP security**: TLS

- **SMTP Auth Type**: LOGIN

- **SMTP username**: username@outlook.com

- **SMTP password**: passwordAccount

These settings ensure that emails are sent correctly to students.

## Support

For questions or issues regarding the SQLab question type, please send an email to the plugin maintainer at nikolaypn2002@gmail.com.
