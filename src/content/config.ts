import { defineCollection, z } from 'astro:content';

const feeProcessSchema = z.object({
  asanaFormUrl: z.string().url().nullable(),
  walkInOnly: z.boolean().default(false),
  notes: z.string().optional(),
});

const professionCollection = defineCollection({
  type: 'content',
  schema: z.object({
    name: z.string(),
    regulator: z.enum(['NYSED', 'OASAS', 'AAMA']),
    form1Pdf: z.string().nullable(),
    form1Revision: z.string().nullable(),
    licensureFee: feeProcessSchema.nullable(),
    examFee: feeProcessSchema.nullable(),
    checklistUrl: z.string().url().nullable(),
    checklistUrls: z.array(z.object({ url: z.string().url(), label: z.string() })).optional(),
    needsContentReview: z.boolean().default(false),
    reviewNotes: z.string().optional(),
    order: z.number(),
  }),
});

const pageCollection = defineCollection({
  type: 'content',
  schema: z.object({
    title: z.string().optional(),
    section: z.string(),
  }),
});

const processStepSchema = z.object({
  title: z.string(),
  mode: z.enum(['on-page', 'offline']),
  body: z.string(),
});

const processCollection = defineCollection({
  type: 'content',
  schema: z.object({
    regulator: z.enum(['NYSED', 'OASAS', 'AAMA']),
    prepHeading: z.string().optional(),
    submitHeading: z.string(),
    submitIntro: z.string().optional(),
    prepSteps: z.array(processStepSchema).default([]),
  }),
});

export const collections = {
  professions: professionCollection,
  page: pageCollection,
  processes: processCollection,
};
