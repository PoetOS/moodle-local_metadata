# moodle-local_metadata

The “local metadata” plugin has been created to allow extra, “metadata” to be defined and
assigned to various context level elements in Moodle. It is an abstraction of the current “user
profile field” functionality in Moodle and contains much of the same code.

The data model includes context level identifiers to allow the same data tables and API’s to be
used for all of the different context levels. The plugin has been designed with the ability to be
adopted into core, and completely replace the current user profile field feature if desired.

In its current concept state, the plugin provides metadata functionality for users, courses,
activity modules, groups, cohorts and course caregories. Other context levels can be added through
the context subplugin. NOTE, that the user metadata provided by the plugin is “in addition to” the
current profile data rather than replacing it. For now, it is really intended to show/prove that
this could replace the user profile data feature.

The plugin provides two subplugin types:
- Data contexts - These subplugins provide a Moodle context to apply metadata to.
- Data field types - These subplugins are the same as the user profile data types plugins.

Since it is a plugin, it can only provide interfaces that are available to plugins. For example,
adding specific data to defined course metadata elements must be done through a different
interface form than the main course settings form. The plugin takes advantages of Moodle
hooks and callbacks that allow screens and menus to be added to, where they exist. If brought
into core, these would be able to be more tightly integrated (this could also be better integrated
if/when the proposed “hook” system provides more hooking points).

The plugin can be retrieved from https://github.com/PoetOS/moodle-local_metadata. It
should be installed into the Moodle “/local/metadata/” directory (the “metadata” subdirectory
needs to be created). Make sure that the “/local/metadata/” directory contains the root of the
plugin.

Goto https://github.com/PoetOS/moodle-local_metadata/blob/master/walkthrough.pdf for a walkthrough
of the plugin in its current state.
